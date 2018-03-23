<?php

  return [
    /*
    * @required: true
    * Create an acount and het Api Key on this site https://www.coinpayments.net
    */
    'public_key' => env('COIN_PAYMENT_PUBLIC_KEY', ''),
    'private_key' => env('COIN_PAYMENT_PRIVATE_KEY', ''),
    /*
    * Image Logo
    */
    'logo' => '/coinpayment.logo.png' // you can use url

  ];
