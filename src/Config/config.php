<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | API Key settings
    |--------------------------------------------------------------------------
    |
    | Set your public & private key
    | please following url for set your public & private key below
    | https://www.coinpayments.net/index.php?cmd=acct_api_keys
    |
    */

    'public_key'    => env('COINPAYMENT_PUBLIC_KEY', ''),
    'private_key'   => env('COINPAYMENT_PRIVATE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware for make payment
    |--------------------------------------------------------------------------
    |
    | Set the custom middleware
    | you can set the "web", "auth" or "auth:guard"
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Checkout live polling
    |--------------------------------------------------------------------------
    |
    | How often the checkout payment modal polls CoinPayments for status
    | updates while a payment is pending (Livewire wire:poll interval, e.g.
    | "3s", "5s", "10s"). Polling stops automatically once the transaction
    | is complete or cancelled.
    |
    */

    'poll_interval' => '5s',

    /*
    |--------------------------------------------------------------------------
    | Admin panel
    |--------------------------------------------------------------------------
    |
    | The standalone admin panel (balances, withdrawals, transactions).
    | Access is protected by the middleware list AND the gate below. When a
    | gate name is set but not defined in your app the panel denies access
    | (fail closed). Define it in a service provider, e.g.:
    |
    |   Gate::define('coinpayment-admin', fn ($user) => $user->is_admin);
    |
    */

    'admin' => [
        'prefix'     => 'coinpayment/admin',

        // The package middleware handles authentication, the gate, and the
        // guest redirect. Keep 'web' for the session. (Add 'auth' only if you
        // prefer your app's own default login redirect over the one below.)
        'middleware' => ['web'],

        'gate'       => 'coinpayment-admin',

        // Where to send unauthenticated visitors — a route name or a URL.
        // null falls back to your app's "login" route (or 401 if none).
        'redirect'   => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction listener job
    |--------------------------------------------------------------------------
    |
    | The job dispatched whenever a transaction is created or updated
    | (via checkout or IPN). Publish it with:
    | php artisan vendor:publish --tag=coinpayment-job
    | Set to null to disable dispatching entirely.
    |
    */

    'listener' => \App\Jobs\CoinpaymentListener::class,

    /*
    |--------------------------------------------------------------------------
    | IPN setting
    |--------------------------------------------------------------------------
    |
    | If you use IPN for get callback response transactions
    | please activate IPN configuration below
    |
    */

    'ipn' => [
        'activate' => env('COINPAYMENT_IPN_ACTIVATE', false),
        'config' => [
            'coinpayment_merchant_id'       => env('COINPAYMENT_MARCHANT_ID', ''),
            'coinpayment_ipn_secret'        => env('COINPAYMENT_IPN_SECRET', ''),
            'coinpayment_ipn_debug_email'   => env('COINPAYMENT_IPN_DEBUG_EMAIL', ''),
        ]
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Currency setting
    |--------------------------------------------------------------------------
    |
    | Please use one currency for convert coin amount
    |
    | USD, CAD, EUR, ARS, AUD, AZN, BGN, BRL, BYN, CHF, CLP, CNY, COP, CZK
    | DKK, GBP, GIP, HKD, HUF, IDR, ILS, INR, IRR, IRT, ISK, JPY, KRW, LAK, MKD, MXN, ZAR,
    | MYR, NGN, NOK, NZD, PEN, PHP, PKR, PLN, RON, RUB, SEK, SGD, THB, TRY, TWD, UAH, VND,
    |
    */

    'default_currency' => env('COINPAYMENT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Coins excluded from fiat conversion
    |--------------------------------------------------------------------------
    |
    | Testnet / no-real-value coins that should NOT be converted to fiat or
    | counted in the total balance. LTCT (Litecoin Testnet) is CoinPayments'
    | sandbox coin, so it is excluded by default.
    |
    */

    'fiat_exclude' => ['LTCT'],

    /*
    |--------------------------------------------------------------------------
    | Header setting
    |--------------------------------------------------------------------------
    */

    'header' => [
        'default' => 'logo',
        'type' => [
            'logo' => '/vendor/coinpayment/coinpayment.logo.png', // path assets file only
            'text' => 'Your payment summary'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Font setting
    |--------------------------------------------------------------------------
    */

    'font' => [
        'family' => "'Roboto', sans-serif",
        'date_format' => 'd/m/y H:i'
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme colors
    |--------------------------------------------------------------------------
    |
    | The checkout & admin pages are fully standalone and never inherit the
    | host application's styles. Only the colors below are customizable;
    | they are injected as CSS variables into the package layout.
    |
    */

    'theme' => [
        'background'   => '#eef1f7',
        'card'         => '#ffffff',
        'text'         => '#0f1729',
        'primary'      => '#2f6fed', // accent: coin selection, totals, links, badges
        'primary_dark' => '#1f57c4',
        'danger'       => '#e02424',
        // Note: the "Pay" button is intentionally a fixed red and is NOT themeable.
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom coin logo
    |--------------------------------------------------------------------------
    */
    
    'logos' => [
        'Bitcoin' => 'https://github.com/hexters/CoinPayment/blob/master/btc.png?raw=true',
        'Velas (Old Chain)' => 'https://www.coinpayments.net/images/coins/VLX.png'
    ],
];
