<?php

namespace Hexters\CoinPayment\Helpers;

use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Traits\InteractsWithListener;
use Illuminate\Support\Facades\Crypt;

class CoinPaymentHelper
{
    use ApiCallTrait;
    use InteractsWithListener;

    /**
     * Generate a checkout link for the given transaction payload.
     *
     * @param  array<string, mixed>  $array
     */
    public function generatelink(array $array): string
    {
        return url('/coinpayment/make/' . $this->transaction_encrypt($array));
    }

    /**
     * Decrypt a checkout payload string back into the raw transaction array.
     *
     * @return array<string, mixed>
     */
    public function getrawtransaction(string $string): array
    {
        return $this->transaction_dencrypt($string);
    }

    /**
     * Encrypt the transaction payload as JSON (no PHP serialization).
     *
     * @param  array<string, mixed>  $array
     */
    protected function transaction_encrypt(array $array): string
    {
        return Crypt::encryptString(json_encode($array, JSON_THROW_ON_ERROR));
    }

    /**
     * Decrypt and decode a transaction payload string.
     *
     * @return array<string, mixed>
     */
    protected function transaction_dencrypt(string $string): array
    {
        return json_decode(Crypt::decryptString($string), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Refresh and return the status for a given transaction id.
     *
     * @return array{status_text: string, status: int}|string
     */
    public function getstatusbytxnid(string $txn_id): array|string
    {
        try {
            $status = $this->api_call('get_tx_info', ['txid' => $txn_id]);

            if (($status['error'] ?? null) !== 'ok') {
                throw new \Exception($status['error'] ?? 'Unknown API error');
            }

            $transaction = CoinpaymentTransaction::where('txn_id', $txn_id)->first();

            if (is_null($transaction)) {
                throw new \Exception('Illegal! Transaction not found in database');
            }

            $transaction->update($status['result']);

            $this->dispatchListener(array_merge($transaction->toArray(), [
                'transaction_type' => 'old',
            ]));

            return [
                'status_text' => $status['result']['status_text'],
                'status'      => $status['result']['status'],
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the merchant account balances.
     *
     * @return array<string, mixed>
     */
    public function getBalances(): array
    {
        return $this->api_call('balances');
    }

    /**
     * Return a fresh transactions query model.
     */
    public function gettransactions(): CoinpaymentTransaction
    {
        return new CoinpaymentTransaction;
    }

    /**
     * Generate a deposit (top up) address for the given currency.
     *
     * @return array<string, mixed>
     */
    public function getDepositAddress(string $currency): array
    {
        return $this->api_call('get_deposit_address', [
            'currency' => $currency,
        ]);
    }

    /**
     * Create a new withdrawal.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function createWithdrawal(array $body): array
    {
        return $this->api_call('create_withdrawal', $body);
    }

    /**
     * Get information about an existing withdrawal.
     *
     * @param  array<string, mixed>|string  $id
     * @return array<string, mixed>
     */
    public function getWithdrawalInfo(array|string $id): array
    {
        return $this->api_call('get_withdrawal_info', is_array($id) ? $id : ['id' => $id]);
    }

    /**
     * List withdrawal history.
     *
     * @param  array<string, mixed>  $params  limit (1-100), start, newer
     * @return array<string, mixed>
     */
    public function getWithdrawalHistory(array $params = []): array
    {
        return $this->api_call('get_withdrawal_history', $params + ['limit' => 25]);
    }

    /**
     * Cancel a withdrawal (only while it is awaiting email confirmation).
     *
     * @return array<string, mixed>
     */
    public function cancelWithdrawal(string $id): array
    {
        return $this->api_call('cancel_withdrawal', ['id' => $id]);
    }
}
