# CoinPayments Legacy for Laravel 11/12/13

[![Latest Stable Version](https://poser.pugx.org/hexters/coinpayment/v/stable)](https://packagist.org/packages/hexters/coinpayment)
[![Total Downloads](https://poser.pugx.org/hexters/coinpayment/downloads)](https://packagist.org/packages/hexters/coinpayment)
[![License](https://poser.pugx.org/hexters/coinpayment/license)](https://packagist.org/packages/hexters/coinpayment)

[![CoinPayments](https://www.coinpayments.net/images/b/banner6_728x90-3.jpg)](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5)

Accept cryptocurrency payments in Laravel through [CoinPayments.net](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5). It gives you a Livewire checkout, live payment status, IPN handling, and an admin panel for balances, withdrawals, and transactions.

v4 is a full rewrite for Laravel 11, 12 and 13 on PHP 8.2+. The frontend runs on Livewire 3 and Alpine, so there is no Node or webpack build step. API calls go through Laravel's `Http` client with TLS verification. The checkout and admin pages render with Tailwind from a CDN and do not pull in your app's CSS; the only thing you can change is the colors.

## Screenshots

![Checkout page](sample/payment-page.png)

<p align="center"><em>Crypto checkout with coin search, a QR code, and the pay address.</em></p>

|  Real-time payment modal  |  Admin dashboard  |
|:-------------------------:|:-----------------:|
| ![Payment modal](sample/modal-payment.png) | ![Wallet dashboard](sample/des-balance.png) |

#### Mobile, with a bottom navigation bar

|  Balances  |  Withdrawals  |  Transactions  |
|:----------:|:-------------:|:--------------:|
| ![Mobile balances](sample/mobile-balance.png) | ![Mobile withdrawals](sample/mobile-withdrawal.png) | ![Mobile transactions](sample/mobile-transaction.png) |

## Features

- A Livewire checkout with coin search, a QR code, and copy-to-clipboard address.
- The payment modal updates on its own while the buyer pays, with a countdown and a poll interval you can configure.
- It understands partial payments and shows how much is still owed instead of just saying "waiting".
- An IPN endpoint plus a queued job you can hook into to fulfil orders.
- A `coinpayment:sync` command for when IPN can't reach you. It also expires payments that ran out of time. Run it from the scheduler.
- A gated admin panel: a wallet dashboard with fiat values, withdrawals (history, detail, cancel), and a transactions table you can search, filter, sort, and page through.
- You can set your own colors and checkout logo, but the pages don't load your app's CSS.
- It works on phones, with a bottom navigation bar.

### Version support
| package | laravel |
|-|-|
| v1.x | 5.6 |
| v2.x | 5.8 – 6.x |
| v3.x | 8.x |
| **v4.x** | **11.x · 12.x · 13.x** |

> Note: this package uses the CoinPayments legacy v1 Merchant API (`coinpayments.net/api.php`, public/private key with HMAC-SHA512), not the newer v2 REST API.

## Requirements

- PHP 8.2 or newer
- Laravel 11, 12 or 13 (it pulls in `livewire/livewire` ^3.5 automatically)
- A database, since the package ships migrations. If you use the listener job you also need a queue with `queue:work` running.
- A [CoinPayments](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5) account with Merchant API keys

## How it works

1. You generate a payment link and the buyer lands on the Livewire checkout.
2. The buyer picks a coin and pays; the modal tracks the status while they do.
3. CoinPayments notifies your app over IPN. If IPN can't reach you, `coinpayment:sync` polls instead.
4. The package verifies the callback, updates the transaction row in your database, and dispatches `App\Jobs\CoinpaymentListener`.
5. Your job fulfils the order based on the transaction status.

## Installation

```bash
composer require hexters/coinpayment
```

Run the interactive installer (writes API keys to `.env`, publishes the config & assets, and migrates):

```bash
php artisan coinpayment:install
```

Or publish manually:

```bash
php artisan vendor:publish --tag=coinpayment-config
php artisan vendor:publish --tag=coinpayment-assets
php artisan vendor:publish --tag=coinpayment-job    # optional: App\Jobs\CoinpaymentListener
php artisan vendor:publish --tag=coinpayment-views  # optional: Blade views
```

Then run the migrations (the installer does this for you):

```bash
php artisan migrate
```

### Environment variables

```dotenv
COINPAYMENT_PUBLIC_KEY=your-public-key
COINPAYMENT_PRIVATE_KEY=your-private-key
COINPAYMENT_CURRENCY=USD            # default fiat currency

# IPN (optional but recommended for production)
COINPAYMENT_IPN_ACTIVATE=true
COINPAYMENT_MARCHANT_ID=your-merchant-id
COINPAYMENT_IPN_SECRET=your-ipn-secret
COINPAYMENT_IPN_DEBUG_EMAIL=you@example.com
```

## Creating a payment link

```php
use Hexters\CoinPayment\CoinPayment;

$transaction = [
    'order_id'     => uniqid(),          // your invoice number (required)
    'amountTotal'  => 37.5,              // total in your default currency (required)
    'note'         => 'Transaction note',
    'buyer_name'   => 'John Doe',
    'buyer_email'  => 'buyer@mail.com',
    'redirect_url' => url('/thank-you'), // after completion
    'cancel_url'   => url('/cart'),      // when cancelled
    'items'        => [                   // optional; if provided, subtotals must sum to amountTotal
        ['itemDescription' => 'Product one', 'itemPrice' => 7.5, 'itemQty' => 1, 'itemSubtotalAmount' => 7.5],
        ['itemDescription' => 'Product two', 'itemPrice' => 10,  'itemQty' => 3, 'itemSubtotalAmount' => 30],
    ],
];

return redirect(CoinPayment::generatelink($transaction));
```

`generatelink()` returns a URL to the Livewire checkout page (`/coinpayment/make/{payload}`) where the buyer picks a coin and pays. The payment modal then updates in real time until complete.

## Reacting to transactions

Publish `App\Jobs\CoinpaymentListener` (tag `coinpayment-job`). It is dispatched, and queued, whenever a transaction is created or updated, whether that comes from the checkout, IPN, a manual sync, or an expiry. This is where you fulfil the order.

> The job implements `ShouldQueue`, so a worker needs to be running (`php artisan queue:work`). Without one the listener never fires, though the database row still gets updated.

The job receives the transaction as an array. Key fields:

| field | description |
|-|-|
| `order_id` | your invoice number (use this to find your order) |
| `txn_id` | CoinPayments transaction id |
| `status` / `status_text` | numeric status (see below) + human text |
| `coin`, `amountf`, `receivedf` | coin, amount due, amount received (floats) |
| `buyer_email`, `buyer_name` | buyer info |
| `payload` | the custom array you passed to `generatelink()` |
| `transaction_type` | `new` (first dispatch) or `old` (a later update) |

### Status codes

| status | meaning |
|-|-|
| `0` | Waiting for buyer funds. If `received` is above 0 but below the amount, treat it as partial |
| `1` | Funds received & confirmed, sending to you |
| `100` | Complete, safe to fulfil |
| `< 0` | Cancelled / timed out / expired |

### Example

```php
// app/Jobs/CoinpaymentListener.php
public function handle(): void
{
    $tx = $this->transaction; // array

    $order = Order::where('invoice', $tx['order_id'])->first();
    if (! $order) {
        return;
    }

    match (true) {
        (int) $tx['status'] >= 100 => $order->markAsPaid(),       // complete
        (int) $tx['status'] < 0    => $order->markAsCancelled(),  // cancelled / expired
        default                    => $order->markAsPending(),     // waiting / confirming
    };
}
```

To point the package at a different job, or turn dispatching off, set it in the config:

```php
// config/coinpayment.php
'listener' => \App\Jobs\CoinpaymentListener::class, // or null to disable
```

## IPN

CoinPayments posts updates to `POST /coinpayment/ipn`. The package registers this route and **already excludes it from CSRF verification**, so no extra setup is needed in Laravel 11+.

Every IPN is verified before it touches your data. The merchant ID is checked, and the raw request body is validated against the `HMAC` header with HMAC-SHA512 and your IPN secret (using `hash_equals`). Invalid callbacks get a `401`. Outbound API calls use Laravel's `Http` client over TLS.

Enable IPN in the config or installer, then set the IPN URL and IPN Secret under Account, Account Settings, Merchant Settings in your CoinPayments dashboard:

![Activate IPN in Merchant Settings](sample/setting.png)

## Syncing without IPN (cron)

When IPN can't reach your app (e.g. local dev), poll instead:

```bash
php artisan coinpayment:sync                 # all pending transactions
php artisan coinpayment:sync --id=CPXXXXXXX   # a single transaction
```

It also marks unpaid transactions as expired once their payment window passes, and dispatches the listener for them. Schedule it in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('coinpayment:sync')->everyMinute()->withoutOverlapping();
```

## Admin panel

A standalone, gate-protected panel ships with the package:

| route | description |
|-|-|
| `/coinpayment/admin` | wallet dashboard: balances with fiat value, top-up and withdraw |
| `/coinpayment/admin/withdrawals` | withdrawal history, detail, single-refresh and cancel |
| `/coinpayment/admin/transactions` | transactions table with search, filter, sort and pagination |

Access is fail-closed. It needs the configured middleware and an authorization gate. Until you define the gate, the panel returns `403`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('coinpayment-admin', fn ($user) => $user->is_admin);
```

```php
// config/coinpayment.php
'admin' => [
    'prefix'     => 'coinpayment/admin',
    'middleware' => ['web'],              // package handles auth + gate; add 'auth' to use your app's default login redirect
    'gate'       => 'coinpayment-admin',
    'redirect'   => null,                 // where to send guests: a route name or URL (null = your app's "login" route)
],
```

To change where guests are sent, point `redirect` at a route name or a URL:

```php
'redirect' => 'login',            // a named route
'redirect' => '/admin/sign-in',   // or a path/URL
```

## Theming (colors only)

The pages do not inherit your application's styles. Colors are the only styling you can change (the checkout logo is configured separately), and they are injected as CSS variables:

```php
// config/coinpayment.php
'theme' => [
    'background'   => '#eef1f7',
    'card'         => '#ffffff',
    'text'         => '#0f1729',
    'primary'      => '#2f6fed',
    'primary_dark' => '#1f57c4',
    'danger'       => '#e02424',
    // Note: the "Pay" button is intentionally a fixed red and is NOT themeable.
],
```

## Checkout logo

The summary card on the checkout shows the CoinPayments logo by default. You can swap it for your own image, or show plain text instead, in the config:

```php
// config/coinpayment.php
'header' => [
    'default' => 'logo', // 'logo' or 'text'
    'type' => [
        'logo' => '/vendor/coinpayment/coinpayment.logo.png', // public path to your image
        'text' => 'Your payment summary',                     // shown when default is 'text'
    ],
],
```

## Other settings

```php
// config/coinpayment.php

// Default fiat currency for display & conversion.
'default_currency' => env('COINPAYMENT_CURRENCY', 'USD'),

// Coins excluded from fiat conversion / totals (testnet coins have no real value).
'fiat_exclude' => ['LTCT'],

// How often the checkout payment modal polls for status updates.
'poll_interval' => '5s',
```

## Manual status check & queries

```php
use Hexters\CoinPayment\CoinPayment;

// Refresh one transaction from CoinPayments (updates the DB, fires the listener).
CoinPayment::getstatusbytxnid('CPDA4VUGSBHYLXXXXXXXXXXXXXXX');

// Eloquent query helper.
CoinPayment::gettransactions()->where('status', 0)->get();
```

### The transaction model

`Hexters\CoinPayment\Entities\CoinpaymentTransaction` is a regular Eloquent model (table `coinpayment_transactions`). Useful columns:

| column | notes |
|-|-|
| `order_id` | your invoice number (unique); link it to your own orders |
| `txn_id` | CoinPayments transaction id (unique) |
| `status`, `status_text` | see the status codes above |
| `coin`, `amount`, `amountf` | coin + amount due |
| `received`, `receivedf`, `recv_confirms` | amount received & confirmations |
| `amount_total_fiat`, `currency_code` | fiat total |
| `address`, `qrcode_url`, `status_url`, `time_expires` | payment details |
| `buyer_name`, `buyer_email` | buyer |
| `payload` | cast to `array`; the custom data you passed to `generatelink()` |

It also has a `items()` relation (`coinpayment_transaction_items`: `description`, `price`, `qty`, `subtotal`, `currency_code`).

```php
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;

$trx = CoinpaymentTransaction::with('items')->where('order_id', $invoice)->first();
```

## Testing on the Litecoin testnet (LTCT)

CoinPayments' sandbox uses LTCT (Litecoin Testnet). Grab free coins from a testnet faucet such as <https://tltc.bitaps.com/> and send them to the invoice address. You don't need a local wallet. LTCT is left out of fiat totals by default.

## Troubleshooting

If you see `Unable to fetch supported coins` or other API errors, open the [CoinPayments API Keys](https://www.coinpayments.net/index.php?cmd=acct_api_keys) page and edit the key permissions. Either whitelist your server IP under *Restrict to IP/IP Range* or leave that field empty.

## Premium features

The package is free to use while you develop. On production and staging servers the full coin list on the checkout, along with the withdrawal and detail views, need a one-time license. You'll be prompted to activate when you go live. [Get a license here](https://buymeacoffee.com/hexters/e/545129).

## Support

Found a bug or need help? Open an issue at **[github.com/hexters/CoinPayment/issues](https://github.com/hexters/CoinPayment/issues)**.

## License

Source-available. Free to use and develop with locally; production/staging use of the premium features requires a paid license (see [Premium features](#premium-features)). © Asep SS (hexters).
