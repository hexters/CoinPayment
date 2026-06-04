---
description: Generate a crypto payment link in Laravel and let buyers pay on a live Livewire checkout with real-time status.
---

# Create a payment

Build the transaction array, pass it to `generatelink()`, and redirect. The buyer lands on the checkout, picks a coin, and pays.

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

`generatelink()` returns a URL to the checkout page (`/coinpayment/make/{payload}`). The payload is encrypted, so amounts and items cannot be tampered with on the way there.

## The payment modal is live

After the buyer confirms, a modal opens and polls CoinPayments until the payment is done. They see waiting, then confirming, then complete, with a countdown to the expiry, without refreshing. You can change the poll interval in the config.

## Partial payments

If a buyer sends less than the invoice (a common testnet quirk when fees are deducted), the modal switches to "remaining to send" and shows the shortfall, instead of sitting on "waiting". In your own code you can detect this with `status == 0 && receivedf > 0 && receivedf < amountf`.

## The logo

The summary card shows the CoinPayments logo by default. Swap it for your own, or use text, in `config('coinpayment.header')`. See [Configuration](/guide/configuration#checkout-logo).
