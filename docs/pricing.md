---
description: Free in development. A one-time $9.9 license unlocks the production features of the CoinPayments Laravel package.
---

# Pricing &amp; license

This is a source-available, open-core package. The core is free to use while you build. A one-time license unlocks the premium features on your live servers.

## Free in development

Install it, read the code, and build your whole integration locally with no limits. Nothing is gated in your local or testing environment.

## One-time license for production

On production and staging, with `APP_DEBUG=false`, a few features ask for a license:

- the full list of accepted coins on the checkout (the free tier allows BTC and LTCT);
- the withdrawal and top-up actions in the admin;
- the transaction and withdrawal detail views.

Everything else keeps working. When you go live, an activation screen appears. Enter the serial you received after purchase and the features unlock on that server.

::: tip What your buyers see
You are never fully blocked. Without a license the checkout still processes payments in BTC (and LTCT on testnet), so money can still come in. The activation prompt is dismissible and the real enforcement is server-side, so you activate once on the server and it is gone before any customer arrives.
:::

<div style="margin:28px 0;padding:24px;border:1px solid var(--vp-c-divider);border-radius:16px;background:var(--vp-c-bg-soft)">
  <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--vp-c-brand-1)">One-time</div>
  <div style="font-size:42px;font-weight:800;font-family:'Schibsted Grotesk',sans-serif;margin:4px 0">$9.9</div>
  <p style="margin:0 0 18px;color:var(--vp-c-text-2)">No subscription. No account. Activate on your own server.</p>
  <a href="https://buymeacoffee.com/hexters/e/545129" target="_blank" rel="noopener" class="VPButton brand" style="text-decoration:none">Buy a license</a>
</div>

## How activation works

After payment you receive a `license-key` file with your serial number. On your production or staging server, open the admin panel or the checkout page, and the activation screen will be there. Paste the serial and click activate. The license is stored on that server and survives deploys.

## Maintenance

The package is actively maintained, and v4 is a full rewrite for Laravel 11, 12 and 13. It uses the CoinPayments legacy v1 Merchant API, which CoinPayments still documents and points Laravel developers to. If that ever changes, it goes in the [changelog](https://github.com/hexters/CoinPayment/blob/master/CHANGELOG.md), not into a surprise.

Questions before you buy? Open an [issue on GitHub](https://github.com/hexters/CoinPayment/issues).
