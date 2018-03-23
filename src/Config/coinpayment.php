<?php

  return [
    /*
    * @required: true
    * Create an acount and het Api Key on this site https://www.coinpayments.net
    */
    'public_key' => env('COIN_PAYMENT_PUBLIC_KEY', ''),
    'private_key' => env('COIN_PAYMENT_PRIVATE_KEY', ''),

    /*
    * Header config
    */
    'header_type' => 'logo', // @option-value: logo|text
      'header_logo' => '/coinpayment.logo.png', // this is a Path Assets file
      'header_text' => 'Your Payment Summary',

  ];
