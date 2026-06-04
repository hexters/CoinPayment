---
description: Keep crypto payment statuses in sync without IPN using the coinpayment:sync command on a cron schedule.
---

# Sync without IPN

When IPN cannot reach your app, for example on local development, poll for status instead:

```bash
php artisan coinpayment:sync                 # all pending transactions
php artisan coinpayment:sync --id=CPXXXXXXX   # a single transaction
```

It also marks unpaid transactions as expired once their payment window passes, and dispatches the listener for them.

## Run it on a schedule

Add it to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('coinpayment:sync')->everyMinute()->withoutOverlapping();
```

In production a single cron entry drives the scheduler:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## A manual check

You can also refresh a single transaction from your own code. This updates the database row and fires the listener:

```php
use Hexters\CoinPayment\CoinPayment;

CoinPayment::getstatusbytxnid('CPDA4VUGSBHYLXXXXXXXXXXXXXXX');
```
