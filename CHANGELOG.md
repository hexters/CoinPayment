# Changelog

All notable changes to **CoinPayments for Laravel** are documented here.
This project adheres to [Semantic Versioning](https://semver.org).

## [4.0.0] - 2026-06-04

A full rewrite and modernization of the package.

### Added
- **Livewire 3 checkout** — coin picker with live search, QR code, copy-to-clipboard address.
- **Real-time payment modal** — polls CoinPayments until complete (configurable `poll_interval`) with a live countdown timer.
- **Partial-payment awareness** — shows the remaining amount to send instead of a confusing "waiting" state.
- **Standalone admin panel** (gate protected):
  - Wallet **dashboard** with per-coin balances and fiat valuation (testnet coins excluded via `fiat_exclude`).
  - **Withdrawals** — history, detail, single-refresh and cancel.
  - **Transactions** — full table with search, status/coin/date filters, sortable columns and pagination.
- **`coinpayment:sync` artisan command** — poll pending transactions when IPN isn't reachable; auto-expires overdue unpaid transactions. Schedulable via cron.
- **Configurable transaction listener** (`config('coinpayment.listener')`) — point at any job or disable it.
- **Theming** — customize colors via `config('coinpayment.theme')`; pages never inherit the host app's CSS.
- **Mobile-friendly** admin with a bottom navigation bar.
- **Configurable admin guest redirect** (`config('coinpayment.admin.redirect')`).

### Changed
- Requires **PHP 8.2+** and **Laravel 11, 12 or 13**.
- Frontend rebuilt with **Livewire 3 + Alpine** and **Tailwind via CDN** — **no Node/Vue/webpack build step**.
- All CoinPayments API calls now use Laravel's **`Http` client with TLS verification** (previously raw cURL with SSL verification disabled).
- IPN callbacks are verified with **HMAC-SHA512** and return proper responses; the IPN route is auto-excluded from CSRF.
- Transaction payloads are stored as **JSON** instead of PHP `serialize`.
- Eloquent model modernized (`casts()` method, anonymous-class migrations).
- `items` is now **optional** when creating a transaction; when provided, subtotals must equal `amountTotal`.
- **License changed** to a source-available, open-core model: free for development; premium features require a paid license in production/staging (see [Premium features](README.md#premium-features)).

### Removed
- Legacy frontend stack: **axios, jQuery, Bootstrap, Vue 2, laravel-mix, moment, sweetalert** and the entire Node build pipeline.
- Vestigial files and dead legacy console commands left over from v3.

### Fixed
- Withdrawal endpoints returned an undefined variable instead of the API response.
- IPN handler now always returns an explicit HTTP response.
- `env()` calls replaced with `config()` for cache-safe configuration.

### Version support

| package | laravel |
|---------|---------|
| 1.x | 5.6 |
| 2.x | 5.8 – 6.x |
| 3.x | 8.x |
| **4.x** | **11.x · 12.x · 13.x** |
