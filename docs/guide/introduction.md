---
description: What the CoinPayments Laravel package is, why to use it, and how a crypto payment flows from link to fulfilment.
---

# Introduction

CoinPayments for Laravel lets you accept cryptocurrency through [CoinPayments.net](https://legacy.coinpayments.net/index.php?ref=3dc0c5875304cc5cc1d98782c2741cb5). You generate a payment link, the buyer pays in the coin of their choice, and your order gets fulfilled from a job. It ships with a Livewire checkout, live payment status, IPN handling, and an admin panel for balances, withdrawals, and transactions.

It is the module CoinPayments themselves point to for Laravel, listed on their [developer code page](https://www.coinpayments.net/apidoc-code).

## Why this one

There are plenty of thin API wrappers. This is the whole checkout, with the parts that usually take a week to get right already built:

- A checkout page your buyer never has to leave, with coin search, a QR code, and a copyable address.
- A payment modal that updates while the buyer pays, including a countdown.
- Partial payments handled properly, so a short payment shows what is still owed.
- IPN verified with HMAC-SHA512, plus a sync command for when IPN cannot reach you.
- An admin panel behind a gate, with balances, withdrawals, and a transactions table.

## What it runs on

This is a full rewrite (v4). The frontend uses Livewire 3 and Alpine with Tailwind from a CDN, so there is no Node or webpack build step in your app. API calls go through Laravel's `Http` client with TLS verification.

| Package | Laravel |
|---------|---------|
| 1.x | 5.6 |
| 2.x | 5.8 – 6.x |
| 3.x | 8.x |
| **4.x** | **11.x · 12.x · 13.x** |

::: tip Legacy v1 API
This package uses the CoinPayments legacy v1 Merchant API (`coinpayments.net/api.php`, public/private key with HMAC-SHA512), not the newer v2 REST API.
:::

## How it works

1. You generate a payment link and the buyer lands on the Livewire checkout.
2. The buyer picks a coin and pays; the modal tracks the status while they do.
3. CoinPayments notifies your app over IPN. If IPN cannot reach you, `coinpayment:sync` polls instead.
4. The package verifies the callback, updates the transaction row in your database, and dispatches `App\Jobs\CoinpaymentListener`.
5. Your job fulfils the order based on the transaction status.

Ready? [Install it](/guide/installation).
