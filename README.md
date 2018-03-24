# CoinPayment

CoinPayment is a laravel module for handle transaction on [CoinPayment](https://www.coinpayments.net/) like a create transaction, history transaction, etc.
![Example](https://github.com/hexters/CoinPayment/blob/master/example.png?raw=true)
## Requirement
- Laravel ^5.5
- PHP >= ^7.1
- GuzzleHttp
- Nesbot/Carbon
## Installation
You can install the package via composer:
```
$ composer require hexters/coinpayment
```
Publishing vendor
```
$ php artisan vendor:publish --tag=coinpayment-publish
```
First you should add trait class on your module ```User``` and use this trait ```Hexters\CoinPayment\Entities\CoinPaymentuserRelation``` check the example below:

```
    <?php
        namespace App;

        use Illuminate\Notifications\Notifiable;
        use Illuminate\Foundation\Auth\User as Authenticatable;
        use Hexters\CoinPayment\Entities\CoinPaymentuserRelation;

        class User extends Authenticatable {
            use Notifiable, CoinPaymentuserRelation;
            ...
```

Add variable Api Key in ```.env``` file
```
COIN_PAYMENT_PUBLIC_KEY=
COIN_PAYMENT_PRIVATE_KEY=
```

Setting schedule for checking transaction succesed in your file ```app > console > kernel```. example:
```
...
    protected function schedule(Schedule $schedule) {
         $schedule->command('coinpayment:transaction-check')
            ->everyFiveMinutes();
    }
...
```
visit the [Documentation Schedule](https://laravel.com/docs/5.6/scheduling)
## Getting Started
Create Button transaction. Example placed on your controller
```
use CoinPayment; // use outside the class
...
    $trx['amountTotal'] = 50; // USD
    $trx['note'] = 'Note for your transaction';

    // # First item
    $trx['items'][0] = [
        'descriptionItem' => 'Product one',
        'priceItem' => 10, // USD
        'qtyItem' => 2,
        'subtotalItem' => 20 // USD
    ];

    // # Secound item
    $trx['items'][1] = [
        'descriptionItem' => 'Product two',
        'priceItem' => 10, // USD
        'qtyItem' => 3,
        'subtotalItem' => 30 // USD
    ];

    $link_transaction = CoinPayment::url_payload($trx);
    ...
    /* On your balde
        <a href="{{ $link_transaction }}" target="_blank">Pay Now</a>
    */
...
```
For integrating with your application, you should crate route path with name ```coinpayment.webhook```  and result Transaction will be send to the route by hook. example create route on your ```web.php```
```
    Route::get('/your/route/name', function(Request $request){
        // Do someting...
        /* === Output data from $request ===

            $request->time_created;
            $request->time_expires;
            $request->status;
            /*  -- Status transaction --
                0   : Waiting for buyer funds
                1   : Funds received and confirmed, sending to you shortly
                100 : Complete,
                -1  : Cancelled / Timed Out
            */
            $request->status_text;
            $request->type;
            $request->coin;
            $request->amount;
            $request->amountf;
            $request->received;
            $request->receivedf;
            $request->recv_confirms;
            $request->payment_address;
            $request->time_completed; // showing if "$request->status" is 100

        */
    })->name('coinpayment.webhook');
```
