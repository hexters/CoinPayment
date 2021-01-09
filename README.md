# CoinPayments v3

[![Latest Stable Version](https://poser.pugx.org/hexters/coinpayment/v/stable)](https://packagist.org/packages/hexters/coinpayment)
[![Total Downloads](https://poser.pugx.org/hexters/coinpayment/downloads)](https://packagist.org/packages/hexters/coinpayment)
[![License](https://poser.pugx.org/hexters/coinpayment/license)](https://packagist.org/packages/hexters/coinpayment)

## Coinpayment Laravel Package

[![Banner](https://www.coinpayments.net/images/b/banner6_728x90-3.jpg)](https://www.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5)

CoinPayment is a Laravel package for handling transactions from [CoinPayment](https://www.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5) like a create transaction, history transaction, etc.

![Example Image](https://github.com/hexters/CoinPayment/blob/master/sample/examplev3.png?raw=true)

### Version support
| version | laravel |
|-|-|
|[v1.1.3](https://github.com/hexters/CoinPayment/releases/tag/v1.1.3)|5.6|
|[v2.0.0](https://github.com/hexters/CoinPayment)|5.8|
|[v2.0.3](https://github.com/hexters/CoinPayment)|^6.x|
|[Current Version](https://github.com/hexters/CoinPayment)|^8.x|

## Installation
You can install this package via composer:
```bash
$ composer require hexters/coinpayment
```

Publishing vendor
```bash
$ php artisan vendor:publish --tag=coinpayment
```

Install CoinPayment configuration
```bash
$ php artisan coinpayment:install
```

Installation finish.

## Getting Started
Create Button transaction. Example placed on your controller
```php
  use Hexters\CoinPayment\CoinPayment;
  . . . 
  /*
  *   @required true
  */
  $transaction['order_id'] = uniqid(); // invoice number
  $transaction['amountTotal'] = (FLOAT) 37.5;
  $transaction['note'] = 'Transaction note';
  $transaction['buyer_name'] = 'Jhone Due';
  $transaction['buyer_email'] = 'buyer@mail.com';
  $transaction['redirect_url'] = url('/back_to_tarnsaction'); // When Transaction was comleted
  $transaction['cancel_url'] = url('/back_to_tarnsaction'); // When user click cancel link


  /*
  *   @required true
  *   @example first item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product one',
    'itemPrice' => (FLOAT) 7.5, // USD
    'itemQty' => (INT) 1,
    'itemSubtotalAmount' => (FLOAT) 7.5 // USD
  ];

  /*
  *   @example second item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product two',
    'itemPrice' => (FLOAT) 10, // USD
    'itemQty' => (INT) 1,
    'itemSubtotalAmount' => (FLOAT) 10 // USD
  ];

  /*
  *   @example third item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product Three',
    'itemPrice' => (FLOAT) 10, // USD
    'itemQty' => (INT) 2,
    'itemSubtotalAmount' => (FLOAT) 20 // USD
  ];

  $transaction['payload'] = [
    'foo' => [
        'bar' => 'baz'
    ]
  ];

  return CoinPayment::generatelink($transaction);
  . . . 
```

## Listening status transaction

Open the Job file `App\Jobs\CoinpaymentListener` for the listen the our transaction and proccess

## Manual check without IPN

This function will execute orders without having to wait for the process from IPN

We can also make cron to run this function if we don't use IPN

```php
use Hexters\CoinPayment\CoinPayment;

. . .

/**
* this is triger function for running Job proccess
*/
return CoinPayment::getstatusbytxnid("CPDA4VUGSBHYLXXXXXXXXXXXXXXX");
// output example: "celled / Timed Out"

```

## Get histories transaction Eloquent
```php

use Hexters\CoinPayment\CoinPayment;

. . .

CoinPayment::gettransactions()->where('status', 0)->get();
```

# IPN Route

Except this path `/coinpayment/ipn` into csrf proccess in `App\Http\Middleware\VerifyCsrfToken` 
```php
. . .
/**
  * The URIs that should be excluded from CSRF verification.
  *
  * @var array
  */
protected $except = [
    '/coinpayment/ipn'
]; 
. . .
```
# Troubleshooting
## Cannot use object of type Illuminate\Http\JsonResponse as array
Visit the [**CoinPayment API Keys**](https://www.coinpayments.net/index.php?cmd=acct_api_keys) page, under *Actions*, click on the *Edit Permissions* button. Enter the IP address of your API endpoint (e.g. your website server) in the *Restrict to IP/IP Range* input. Leaving it empty, may cause this error to occur.

# Donate
If this Laravel package was useful to you, please consider donating some coffee ☕☕☕☕

Bitcoin (BTC): `1388MHjeHmq6kUC7WpSS6pPtgG7hm7fCau`
