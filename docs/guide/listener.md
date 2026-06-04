---
description: Fulfil orders from a queued job. The transaction payload, CoinPayments status codes, and the Eloquent model.
---

# The listener job

Publish `App\Jobs\CoinpaymentListener` (tag `coinpayment-job`). It is dispatched, and queued, whenever a transaction is created or updated, whether that comes from the checkout, IPN, a manual sync, or an expiry. This is where you fulfil the order.

::: warning Run a worker
The job implements `ShouldQueue`, so a worker needs to be running (`php artisan queue:work`). Without one the listener never fires, though the database row still gets updated.
:::

## The payload

The job receives the transaction as an array. The fields you will reach for:

| field | description |
|-------|-------------|
| `order_id` | your invoice number; link it to your own orders |
| `txn_id` | CoinPayments transaction id |
| `status` / `status_text` | numeric status (below) and human text |
| `coin`, `amountf`, `receivedf` | coin, amount due, amount received (floats) |
| `buyer_email`, `buyer_name` | buyer info |
| `payload` | the custom array you passed to `generatelink()` |
| `transaction_type` | `new` on the first dispatch, `old` on later updates |

## Status codes

| status | meaning |
|--------|---------|
| `0` | Waiting for buyer funds. If `received` is above 0 but below the amount, treat it as partial |
| `1` | Funds received and confirmed, sending to you |
| `100` | Complete, safe to fulfil |
| `< 0` | Cancelled, timed out, or expired |

## Example

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

## The transaction model

`Hexters\CoinPayment\Entities\CoinpaymentTransaction` is a regular Eloquent model (table `coinpayment_transactions`). Useful columns:

| column | notes |
|--------|-------|
| `order_id` | your invoice number (unique) |
| `txn_id` | CoinPayments transaction id (unique) |
| `status`, `status_text` | see the status codes above |
| `coin`, `amount`, `amountf` | coin and amount due |
| `received`, `receivedf`, `recv_confirms` | amount received and confirmations |
| `amount_total_fiat`, `currency_code` | fiat total |
| `address`, `qrcode_url`, `status_url`, `time_expires` | payment details |
| `buyer_name`, `buyer_email` | buyer |
| `payload` | cast to `array`; the custom data you passed to `generatelink()` |

It also has an `items()` relation.

```php
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;

$trx = CoinpaymentTransaction::with('items')->where('order_id', $invoice)->first();
```
