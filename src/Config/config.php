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
    | Currencies setting
    |--------------------------------------------------------------------------
    |
    | please use one currencies for convert coin amount
    | USD, CAD, EUR, ARS, AUD, AZN, BGN, BRL, BYN, CHF, CLP, CNY, COP, CZK
    | DKK, GBP, GIP, HKD, HUF, IDR, ILS, INR, IRR, IRT, ISK, JPY, KRW, LAK, MKD, MXN, ZAR,
    | MYR, NGN, NOK, NZD, PEN, PHP, PKR, PLN, RON, RUB, SEK, SGD, THB, TRY, TWD, UAH, VND,
    |
    */

    'default_currency' => env('COINPAYMENT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Header setting
    |--------------------------------------------------------------------------
    */

    'header' => [
        'default' => 'logo',
        'type' => [
            'logo' => '/coinpayment.logo.png', // path assets file only
            'text' => 'Your payment summary'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Font setting
    |--------------------------------------------------------------------------
    */

    'font' => [
        'family' => "'Roboto', sans-serif"
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom coin logo
    |--------------------------------------------------------------------------
    */
    
    'logos' => [
        'Bitcoin' => 'https://github.com/hexters/CoinPayment/blob/master/btc.png?raw=true',
    ]
];
