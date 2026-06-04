---
description: Configure the CoinPayments Laravel package — checkout logo, theme colors, default currency, and the polling interval.
---

# Configuration

Everything lives in `config/coinpayment.php` after you publish it.

## Checkout logo

The summary card on the checkout shows the CoinPayments logo by default. Swap it for your own image, or show plain text instead:

```php
'header' => [
    'default' => 'logo', // 'logo' or 'text'
    'type' => [
        'logo' => '/vendor/coinpayment/coinpayment.logo.png', // public path to your image
        'text' => 'Your payment summary',                     // shown when default is 'text'
    ],
],
```

## Theme colors

The checkout and admin pages do not inherit your application's styles. Colors are the only styling you can change, and they are injected as CSS variables:

```php
'theme' => [
    'background'   => '#eef1f7',
    'card'         => '#ffffff',
    'text'         => '#0f1729',
    'primary'      => '#2f6fed',
    'primary_dark' => '#1f57c4',
    'danger'       => '#e02424',
    // Note: the "Pay" button is intentionally a fixed red and is not themeable.
],
```

## Other settings

```php
// Default fiat currency for display and conversion.
'default_currency' => env('COINPAYMENT_CURRENCY', 'USD'),

// Coins excluded from fiat conversion and totals (testnet coins have no real value).
'fiat_exclude' => ['LTCT'],

// How often the checkout payment modal polls for status updates.
'poll_interval' => '5s',
```
