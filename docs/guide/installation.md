---
description: Install the CoinPayments Laravel package with Composer, publish the config and assets, set your API keys, and migrate.
---

# Installation

## Requirements

- PHP 8.2 or newer
- Laravel 11, 12 or 13 (it pulls in `livewire/livewire` ^3.5 automatically)
- A database, since the package ships migrations. If you use the listener job you also need a queue with `queue:work` running.
- A [CoinPayments](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5) account with Merchant API keys

## Install

```bash
composer require hexters/coinpayment
```

Run the interactive installer. It writes your API keys to `.env`, publishes the config and assets, and migrates:

```bash
php artisan coinpayment:install
```

Or publish things by hand:

```bash
php artisan vendor:publish --tag=coinpayment-config
php artisan vendor:publish --tag=coinpayment-assets
php artisan vendor:publish --tag=coinpayment-job    # optional: App\Jobs\CoinpaymentListener
php artisan vendor:publish --tag=coinpayment-views  # optional: Blade views
php artisan migrate
```

## Environment variables

```dotenv
COINPAYMENT_PUBLIC_KEY=your-public-key
COINPAYMENT_PRIVATE_KEY=your-private-key
COINPAYMENT_CURRENCY=USD            # default fiat currency

# IPN (recommended for production)
COINPAYMENT_IPN_ACTIVATE=true
COINPAYMENT_MARCHANT_ID=your-merchant-id
COINPAYMENT_IPN_SECRET=your-ipn-secret
COINPAYMENT_IPN_DEBUG_EMAIL=you@example.com
```

Next: [create your first payment](/guide/checkout).
