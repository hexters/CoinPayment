<?php

namespace Hexters\CoinPayment\Traits;

use Illuminate\Support\Facades\Http;

trait ApiCallTrait
{
    /**
     * Call the CoinPayments.net Merchant API.
     *
     * @param  string  $cmd   API command (e.g. "rates", "create_transaction").
     * @param  array<string, mixed>  $req
     * @return array<string, mixed>
     */
    public function api_call(string $cmd, array $req = []): array
    {
        $publicKey  = config('coinpayment.public_key');
        $privateKey = config('coinpayment.private_key');

        $req['version'] = 1;
        $req['cmd']     = $cmd;
        $req['key']     = $publicKey;
        $req['format']  = 'json';

        // CoinPayments signs the raw urlencoded POST body, so we build it
        // ourselves and reuse the exact same string for the HMAC.
        $postData = http_build_query($req, '', '&');
        $hmac     = hash_hmac('sha512', $postData, (string) $privateKey);

        try {
            $response = Http::asForm()
                ->withHeaders(['HMAC' => $hmac])
                ->withBody($postData, 'application/x-www-form-urlencoded')
                ->post('https://www.coinpayments.net/api.php');
        } catch (\Throwable $e) {
            return ['error' => 'HTTP error: ' . $e->getMessage()];
        }

        if ($response->failed()) {
            return ['error' => 'HTTP error: status ' . $response->status()];
        }

        $decoded = json_decode($response->body(), true);

        if (! is_array($decoded) || $decoded === []) {
            return ['error' => 'Unable to parse JSON result (' . json_last_error_msg() . ')'];
        }

        return $decoded;
    }
}
