---
description: Test crypto payments on the Litecoin testnet (LTCT) with free coins from CoinPayments, end to end.
---

# Testnet testing

CoinPayments' sandbox uses LTCT (Litecoin Testnet), and they give you 5 LTCT to test with.

## Claim your 5 LTCT

1. Open [legacy.coinpayments.net/acct-balances](https://legacy.coinpayments.net/acct-balances).
2. Scroll to the bottom and find **Litecoin Testnet2**.
3. Press the **Get LTCT** button. The 5 LTCT lands in your account balance.

## Run a full payment test

1. Create a transaction from your checkout page so you have an invoice to pay.
2. Withdraw LTCT from your balance to that invoice's address. You can do this from the Balances tab in the admin panel.
3. Check your email and click the link to confirm the withdrawal.
4. Wait for it to confirm. The status updates over IPN, or run `php artisan coinpayment:sync`.

LTCT is left out of fiat totals by default.

## Troubleshooting

If you see `Unable to fetch supported coins` or other API errors, open the [CoinPayments API Keys](https://www.coinpayments.net/index.php?cmd=acct_api_keys) page and edit the key permissions. Either whitelist your server IP under *Restrict to IP/IP Range* or leave that field empty.
