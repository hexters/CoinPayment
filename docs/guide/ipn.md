---
description: Handle CoinPayments IPN callbacks in Laravel, verified with HMAC-SHA512, with no extra CSRF setup.
---

# IPN callbacks

CoinPayments posts updates to `POST /coinpayment/ipn`. The package registers this route and already excludes it from CSRF verification, so there is nothing extra to wire up in Laravel 11+.

## Security

Every IPN is verified before it touches your data. The merchant ID is checked, and the raw request body is validated against the `HMAC` header with HMAC-SHA512 and your IPN secret (using `hash_equals`). Invalid callbacks get a `401`. Outbound API calls use Laravel's `Http` client over TLS.

## Turn it on

Enable IPN in the config or installer, then set the IPN URL and IPN Secret under Account, Account Settings, Merchant Settings in your CoinPayments dashboard:

![Activate IPN in Merchant Settings](/setting.png)

::: tip Local development
IPN cannot reach `localhost`. While developing, use the [sync command](/guide/sync) to poll for status instead, or expose your app with a tunnel.
:::
