# CoinPayments v2

[![Latest Stable Version](https://poser.pugx.org/hexters/coinpayment/v/stable)](https://packagist.org/packages/hexters/coinpayment)
[![Total Downloads](https://poser.pugx.org/hexters/coinpayment/downloads)](https://packagist.org/packages/hexters/coinpayment)
[![License](https://poser.pugx.org/hexters/coinpayment/license)](https://packagist.org/packages/hexters/coinpayment)

## New transform coinpayment package

CoinPayment is a Laravel module for handling transactions from [**CoinPayment**](https://www.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5) like a create transaction, history transaction, etc.

![Example Image](https://github.com/hexters/CoinPayment/blob/master/examplev2.png?raw=true)

### Version support
| version | laravel |
|-|-|
|[v1.1.3](https://github.com/hexters/CoinPayment/releases/tag/v1.1.3)|5.6|
|[v2.0.0](https://github.com/hexters/CoinPayment)|^5.8|

## Requirement
* Laravel 5.7
* PHP >= ^7.2

## Installation
You can install this package via composer:
```
$ composer require hexters/coinpayment
```

Publishing vendor
```
$ php artisan vendor:publish --tag=coinpayment
```

Install CoinPayment configuration
```
$ php artisan coinpayment:install
```

Installation finish.

## Getting Started
Create Button transaction. Example placed on your controller
```
  . . . 
  /*
  *   @required true
  */
  $transaction['amountTotal'] = 30;
  $transaction['note'] = 'Note for your transaction';
  $transaction['buyer_email'] = 'buyer@mailinator.com';
  $transaction['redirect_url'] = url('/back_to_tarnsaction');

  /*
  *   @required true
  *   @example first item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product one',
    'itemPrice' => 10, // USD
    'itemQty' => 1,
    'itemSubtotalAmount' => 10 // USD
  ];

  /*
  *   @example second item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product two',
    'itemPrice' => 10, // USD
    'itemQty' => 1,
    'itemSubtotalAmount' => 10 // USD
  ];

  /*
  *   @example third item
  */
  $transaction['items'][] = [
    'itemDescription' => 'Product Three',
    'itemPrice' => 10, // USD
    'itemQty' => 1,
    'itemSubtotalAmount' => 10 // USD
  ];

  $transaction['payload'] = [
    'foo' => [
        'bar' => 'baz'
    ]
  ];

  return \CoinPayment::generatelink($transaction);
  . . . 
```

## Listening status transaction

Open the Job file `App\Jobs\CoinpaymentListener` for the listen the our transaction and proccess

## Manual check without IPN

This function will execute orders without having to wait for the process from IPN

We can also make cron to run this function if we don't use IPN

```
/**
* this is triger function for running Job proccess
*/
return \CoinPayment::getstatusbytxnid("CPDA4VUGSBHYLXXXXXXXXXXXXXXX");
/**
  output example: "celled / Timed Out"
*/
```

## Get histories transaction Eloquent
```
\CoinPayment::gettransactions()->where('status', 0')->get();
```

Gift me a coffee ☕☕☕☕
```
BTC: 1388MHjeHmq6kUC7WpSS6pPtgG7hm7fCau 
```
