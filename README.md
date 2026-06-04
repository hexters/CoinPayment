# CoinPayments Legacy for Laravel 11/12/13

[![Latest Stable Version](https://poser.pugx.org/hexters/coinpayment/v/stable)](https://packagist.org/packages/hexters/coinpayment)
[![Total Downloads](https://poser.pugx.org/hexters/coinpayment/downloads)](https://packagist.org/packages/hexters/coinpayment)
[![Tests](https://github.com/hexters/CoinPayment/actions/workflows/tests.yml/badge.svg)](https://github.com/hexters/CoinPayment/actions/workflows/tests.yml)
[![License](https://poser.pugx.org/hexters/coinpayment/license)](https://packagist.org/packages/hexters/coinpayment)

[![CoinPayments](https://www.coinpayments.net/images/b/banner6_728x90-3.jpg)](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5)

Accept cryptocurrency payments in Laravel through [CoinPayments.net](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5). It gives you a Livewire checkout, live payment status, IPN handling, and an admin panel for balances, withdrawals, and transactions. This is the module CoinPayments points to for Laravel on their [developer code page](https://www.coinpayments.net/apidoc-code).

It uses the CoinPayments legacy v1 Merchant API.

## Documentation

Full guides, configuration, and screenshots are on the docs site:

### **https://hexters.github.io/CoinPayment/**

![Checkout](sample/payment-page.png)

## Install

```bash
composer require hexters/coinpayment
php artisan coinpayment:install
```

Then generate a payment link and redirect to it:

```php
use Hexters\CoinPayment\CoinPayment;

return redirect(CoinPayment::generatelink([
    'order_id'     => uniqid(),
    'amountTotal'  => 37.5,
    'buyer_email'  => 'buyer@mail.com',
    'redirect_url' => url('/thank-you'),
]));
```

The full setup, the listener job, IPN, the admin panel, and every config option are covered in the [documentation](https://hexters.github.io/CoinPayment/guide/introduction).

## What you get

- A Livewire checkout with coin search, a QR code, and a copyable address
- A payment modal that updates in real time, with a countdown and partial-payment handling
- IPN verified with HMAC-SHA512, plus a `coinpayment:sync` command for when IPN cannot reach you
- A gate-protected admin panel: balances with fiat values, withdrawals, and a searchable transactions table
- Runs on Laravel 11, 12 and 13 with no Node build step

## Pricing

Free to use while you develop. On production and staging, a one-time license unlocks every coin on the checkout plus withdrawals and the detail views. See [pricing](https://hexters.github.io/CoinPayment/pricing) or [get a license](https://buymeacoffee.com/hexters/e/545129).

## Support

Found a bug or need help? Open an issue at [github.com/hexters/CoinPayment/issues](https://github.com/hexters/CoinPayment/issues).

## License

Source-available. Free to use and develop with locally; production and staging use of the premium features requires a paid license. © 2018-2026 Asep SS (hexters).
