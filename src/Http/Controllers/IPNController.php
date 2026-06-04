<?php

namespace Hexters\CoinPayment\Http\Controllers;

use Hexters\CoinPayment\Emails\IPNErrorMail as SendEmail;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Traits\ApiCallTrait;
use Hexters\CoinPayment\Traits\InteractsWithListener;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;

class IPNController extends Controller
{
    use ApiCallTrait;
    use InteractsWithListener;

    public function __invoke(Request $request)
    {
        $merchantId = config('coinpayment.ipn.config.coinpayment_merchant_id');
        $ipnSecret  = config('coinpayment.ipn.config.coinpayment_ipn_secret');
        $debugEmail = config('coinpayment.ipn.config.coinpayment_ipn_debug_email');

        // Validate merchant id.
        if (! empty($request->merchant) && $request->merchant !== trim((string) $merchantId)) {
            return $this->reject('No or incorrect Merchant ID passed', $debugEmail);
        }

        $body = $request->getContent();
        if (empty($body)) {
            return $this->reject('Error reading POST data', $debugEmail);
        }

        // Verify HMAC signature against the raw request body.
        $hmac = hash_hmac('sha512', $body, trim((string) $ipnSecret));
        if (! hash_equals($hmac, (string) $request->server('HTTP_HMAC'))) {
            return $this->reject('HMAC signature does not match', $debugEmail);
        }

        $transaction = CoinpaymentTransaction::where('txn_id', $request->txn_id)->first();

        if (! $transaction) {
            $this->notify($debugEmail, 'Txn ID ' . $request->txn_id . ' not found in database');

            return response('IPN OK (unknown txn)', 200);
        }

        $info = $this->api_call('get_tx_info', ['txid' => $request->txn_id]);

        if (($info['error'] ?? null) !== 'ok') {
            $this->notify($debugEmail, now()->toDateTimeString() . ' ' . ($info['error'] ?? 'API error'));

            return response('IPN OK (api error)', 200);
        }

        try {
            $transaction->update($info['result']);
        } catch (\Exception $e) {
            $this->notify($debugEmail, now()->toDateTimeString() . ' ' . $e->getMessage());
        }

        $this->dispatchListener(array_merge($transaction->fresh()->toArray(), [
            'transaction_type' => 'old',
        ]));

        return response('IPN OK', 200);
    }

    protected function reject(string $message, ?string $debugEmail)
    {
        $this->notify($debugEmail, $message);

        return response($message, 401);
    }

    protected function notify(?string $email, string $message): void
    {
        if (! empty($email)) {
            Mail::to($email)->send(new SendEmail(['message' => $message]));
        }
    }
}
