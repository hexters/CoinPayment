<?php

namespace Hexters\CoinPayment\Console;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Traits\InteractsWithListener;
use Illuminate\Console\Command;

class SyncTransactionsCommand extends Command
{
    use InteractsWithListener;

    /**
     * @var string
     */
    protected $signature = 'coinpayment:sync {--id= : Only sync a single txn_id}';

    /**
     * @var string
     */
    protected $description = 'Poll CoinPayments for pending transaction status updates (use when IPN is unavailable)';

    public function handle(CoinPaymentHelper $helper): int
    {
        $query = CoinpaymentTransaction::whereNotNull('txn_id');

        if ($id = $this->option('id')) {
            $query->where('txn_id', $id);
        }

        // Pending = status between 0 (waiting) and 99. 100 = complete, < 0 = cancelled/timeout/expired.
        $pending = $query->get()->filter(function ($trx) {
            $status = (int) $trx->status;

            return $status >= 0 && $status < 100;
        });

        if ($pending->isEmpty()) {
            $this->info('No pending transactions to sync.');

            return self::SUCCESS;
        }

        $this->info("Syncing {$pending->count()} transaction(s)…");

        foreach ($pending as $trx) {
            $result = $helper->getstatusbytxnid($trx->txn_id);

            // Re-read: getstatusbytxnid updates its own model instance, not this one.
            $trx->refresh();

            // Expire unpaid transactions whose payment window has closed.
            if ($this->shouldExpire($trx)) {
                $this->expire($trx);
                $this->warn(sprintf('  %s  →  [-1] Expired (past due, unpaid)', $trx->txn_id));

                continue;
            }

            if (is_array($result)) {
                $this->line(sprintf('  %s  →  [%d] %s', $trx->txn_id, $result['status'], $result['status_text']));
            } else {
                $this->warn(sprintf('  %s  →  %s', $trx->txn_id, $result));
            }
        }

        return self::SUCCESS;
    }

    /**
     * A transaction expires when it is still waiting (status 0), nothing has
     * been received, and its time_expires timestamp has already passed.
     */
    protected function shouldExpire(CoinpaymentTransaction $trx): bool
    {
        $expires = (int) $trx->time_expires;

        return (int) $trx->status === 0
            && (float) $trx->receivedf <= 0
            && $expires > 0
            && $expires < now()->getTimestamp();
    }

    protected function expire(CoinpaymentTransaction $trx): void
    {
        $trx->update([
            'status'      => -1,
            'status_text' => 'Expired / Timed out',
        ]);

        $this->dispatchListener(array_merge($trx->fresh()->toArray(), [
            'transaction_type' => 'old',
        ]));
    }
}
