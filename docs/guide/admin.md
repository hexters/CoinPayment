---
description: A standalone, gate-protected admin panel for crypto balances, withdrawals, and a searchable transactions table.
---

# Admin panel

A standalone, gate-protected panel ships with the package. It does not load your app's CSS, and it works on phones with a bottom navigation bar.

![Wallet dashboard](/des-balance.png)

| Route | What it does |
|-------|--------------|
| `/coinpayment/admin` | wallet dashboard: balances with fiat value, top-up and withdraw |
| `/coinpayment/admin/withdrawals` | withdrawal history, detail, single-refresh and cancel |
| `/coinpayment/admin/transactions` | transactions table with search, filter, sort and pagination |

## Access control

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

## Balances

The dashboard lists each coin's balance and converts it to your default currency using live rates. You can top up (reveal a deposit address) and withdraw. Testnet coins are left out of the fiat total.

## Withdrawals

CoinPayments emails you a confirmation link for each withdrawal. After you submit one from the Balances tab, the package sends you to the Withdrawals page with a reminder to check that email. From there you can review the history, refresh a single withdrawal, or cancel one that is still awaiting confirmation.

## Transactions

A real table with live search, status, coin and date filters, sortable columns, pagination, and a detail view. All of the table state lives in the URL, so a filtered view can be bookmarked or shared.
