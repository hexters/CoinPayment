<?php

namespace Hexters\CoinPayment\Livewire;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Licensing\License;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Traits\InteractsWithListener;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('coinpayment::layouts.app')]
#[Title('Make Transaction')]
class FormTransaction extends Component
{
    use ApiCallTrait;
    use InteractsWithListener;

    /** Raw encrypted payload string from the route. */
    public string $payload = '';

    /** Decrypted checkout payload. @var array<string, mixed> */
    public array $data = [];

    /** Accepted coins for display. @var array<int, array<string, mixed>> */
    public array $acceptedCoins = [];

    /** Currently selected coin. @var array<string, mixed> */
    public array $defaultCoin = [];

    public string $defaultCurrency = '';

    /** Header config. @var array<string, mixed> */
    public array $header = [];

    /** Existing or created transaction. @var array<string, mixed>|null */
    public ?array $transaction = null;

    public string $checkoutUrl = '';

    public ?string $errorMessage = null;

    /** True when the page failed to load (bad payload / rates) vs. an action error. */
    public bool $fatal = false;

    public function mount(string $payload): void
    {
        $this->payload     = $payload;
        $this->checkoutUrl = request()->fullUrl();

        try {
            $helper     = app(CoinPaymentHelper::class);
            $this->data = $helper->getrawtransaction($payload);

            $this->validatePayload();

            $rates = $this->buildRates((float) $this->data['amountTotal']);

            $this->acceptedCoins   = $rates['accepted_coin'];
            $this->defaultCoin     = $rates['default_coin'];
            $this->defaultCurrency = config('coinpayment.default_currency');
            $this->header          = config('coinpayment.header');
            $this->transaction     = $this->existingTransaction($this->data['order_id'] ?? null);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->fatal = true;
        }
    }

    public function setBilling(string $iso): void
    {
        foreach ($this->acceptedCoins as $coin) {
            if ($coin['iso'] === $iso) {
                $this->defaultCoin = $coin;

                return;
            }
        }
    }

    /**
     * Create the transaction on CoinPayments for the selected coin.
     * Amounts and items are taken from the server-side payload, never the client.
     */
    public function createTransaction(): void
    {
        $this->errorMessage = null;

        try {
            $coinIso = $this->defaultCoin['iso'] ?? null;

            if (empty($coinIso)) {
                throw new \Exception('Please choose a coin first.');
            }

            $orderId = $this->data['order_id'] ?? null;

            if (empty($orderId)) {
                throw new \Exception('Order ID cannot be empty.');
            }

            DB::beginTransaction();

            $existing = CoinpaymentTransaction::where('order_id', $orderId)
                ->whereNotNull('txn_id')
                ->first();

            if ($existing) {
                throw new \Exception(
                    'Order ID: ' . $existing->order_id . ' already exists, current status is ' . $existing->status_text
                );
            }

            $create = $this->api_call('create_transaction', [
                'amount'      => (float) $this->data['amountTotal'],
                'currency1'   => config('coinpayment.default_currency'),
                'currency2'   => $coinIso,
                'buyer_email' => $this->data['buyer_email'] ?? null,
            ]);

            if (($create['error'] ?? null) !== 'ok') {
                throw new \Exception($create['error'] ?? 'Unable to create transaction');
            }

            $info = $this->api_call('get_tx_info', ['txid' => $create['result']['txn_id']]);

            if (($info['error'] ?? null) !== 'ok') {
                throw new \Exception($info['error'] ?? 'Unable to read transaction info');
            }

            $result = array_merge($create['result'], $info['result'], [
                'order_id'          => $orderId,
                'amount_total_fiat' => $this->data['amountTotal'],
                'payload'           => $this->data,
                'buyer_name'        => $this->data['buyer_name'] ?? '-',
                'buyer_email'       => $this->data['buyer_email'] ?? '-',
                'currency_code'     => config('coinpayment.default_currency'),
                'redirect_url'      => $this->data['redirect_url'] ?? null,
                'cancel_url'        => $this->data['cancel_url'] ?? null,
                'checkout_url'      => $this->checkoutUrl,
            ]);

            $transaction = CoinpaymentTransaction::whereNull('txn_id')
                ->where('order_id', $orderId)
                ->first();

            if ($transaction) {
                $transaction->update($result);
            } else {
                $transaction = CoinpaymentTransaction::create($result);

                foreach ($this->data['items'] ?? [] as $item) {
                    $transaction->items()->create([
                        'description'   => $item['itemDescription'],
                        'price'         => $item['itemPrice'],
                        'qty'           => $item['itemQty'],
                        'subtotal'      => $item['itemSubtotalAmount'],
                        'currency_code' => config('coinpayment.default_currency'),
                    ]);
                }
            }

            $this->dispatchListener(array_merge($result, ['transaction_type' => 'new']));

            DB::commit();

            $this->transaction = $this->existingTransaction($orderId);
            $this->dispatch('transaction-created');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorMessage = $e->getMessage();
            $this->dispatch('transaction-failed', message: $e->getMessage());
        }
    }

    /**
     * Poll CoinPayments for the latest status while the payment modal is open.
     * No-ops once the transaction reaches a final state so polling stops.
     */
    public function pollStatus(): void
    {
        if (empty($this->transaction['txn_id'])) {
            return;
        }

        $status = (int) ($this->transaction['status'] ?? 0);

        if ($status >= 100 || $status < 0) {
            return; // already complete / cancelled
        }

        app(CoinPaymentHelper::class)->getstatusbytxnid($this->transaction['txn_id']);

        $this->transaction = $this->existingTransaction($this->data['order_id'] ?? null);

        if ((int) ($this->transaction['status'] ?? 0) >= 100) {
            $this->dispatch('transaction-completed');
        }
    }

    public function render()
    {
        return view('coinpayment::livewire.form-transaction');
    }

    // ---------------------------------------------------------------------
    // Internal helpers
    // ---------------------------------------------------------------------

    protected function validatePayload(): void
    {
        foreach (['amountTotal', 'note'] as $key) {
            if (empty($this->data[$key])) {
                throw new \Exception("Oops!, index [{$key}] not found in payload!");
            }
        }

        // "items" is optional. When provided, the subtotals must add up to amountTotal.
        if (! empty($this->data['items'])) {
            if (! is_array($this->data['items'])) {
                throw new \Exception('Payload [items] must be an array.');
            }

            $sum = array_sum(array_map(
                fn ($item) => (float) ($item['itemSubtotalAmount'] ?? 0),
                $this->data['items']
            ));

            if (abs($sum - (float) $this->data['amountTotal']) > 0.00000001) {
                throw new \Exception(
                    "Item subtotal total ({$sum}) does not match amountTotal ({$this->data['amountTotal']})."
                );
            }
        }
    }

    /**
     * Fetch accepted coins from CoinPayments and compute amounts for the order.
     *
     * @return array{accepted_coin: array<int, array<string, mixed>>, default_coin: array<string, mixed>}
     */
    protected function buildRates(float $amount): array
    {
        $rates = $this->api_call('rates', ['accepted' => 1]);

        if (strtolower($rates['error'] ?? '') !== 'ok') {
            throw new \Exception('Unable to fetch supported coins from CoinPayments.');
        }

        $rates = $rates['result'];
        $currency = config('coinpayment.default_currency');

        if (empty($rates['BTC'])) {
            throw new \Exception('Rate BTC not found! Please enable BTC as default rate coin.');
        }

        if (empty($rates[$currency])) {
            throw new \Exception("Fiat {$currency} is not supported. Please contact CoinPayments support.");
        }

        $logos      = config('coinpayment.logos', []);
        $rateAmount = (float) $rates[$currency]['rate_btc'] * $amount;

        $accepted = [];

        foreach ($rates as $iso => $value) {
            $isFiat   = (int) $value['is_fiat'] === 1;
            $accepted_flag = (int) ($value['accepted'] ?? 0) === 1;

            if ($isFiat || ! $accepted_flag) {
                continue;
            }

            $rate = (float) $value['rate_btc'] > 0 ? ($rateAmount / (float) $value['rate_btc']) : 0;

            $accepted[] = [
                'name'     => $value['name'],
                'amount'   => $rate > 0 ? number_format($rate, 8, '.', '') : '-',
                'iso'      => $iso,
                'icon'     => $logos[$value['name']] ?? 'https://www.coinpayments.net/images/coins/' . $this->coinImage($iso) . '.png',
                'selected' => $iso === 'BTC',
            ];
        }

        if (License::locked()) {
            $accepted = array_values(array_filter(
                $accepted,
                fn ($c) => in_array($c['iso'], License::FREE_COINS, true)
            ));
        }

        $default = collect($accepted)->firstWhere('selected', true) ?? ($accepted[0] ?? []);

        return [
            'accepted_coin' => $accepted,
            'default_coin'  => $default,
        ];
    }

    protected function coinImage(string $iso): string
    {
        return match ($iso) {
            'BTC.LN'     => 'BTCLN',
            'USDT.ERC20' => 'USDT',
            default      => $iso,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function existingTransaction(?string $orderId): ?array
    {
        if (empty($orderId)) {
            return null;
        }

        $transaction = CoinpaymentTransaction::whereNotNull('txn_id')
            ->where('order_id', $orderId)
            ->first();

        return $transaction?->only([
            'address', 'amount', 'amountf', 'coin', 'confirms_needed', 'payment_address',
            'qrcode_url', 'received', 'receivedf', 'recv_confirms', 'status', 'status_text',
            'status_url', 'timeout', 'time_expires', 'txn_id', 'type',
        ]);
    }
}
