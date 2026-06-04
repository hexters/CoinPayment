<?php

use Hexters\CoinPayment\Http\Controllers\IPNController;
use Hexters\CoinPayment\Http\Middleware\CoinpaymentAuthMiddleware;
use Hexters\CoinPayment\Livewire\Admin\Balances;
use Hexters\CoinPayment\Livewire\Admin\Transactions;
use Hexters\CoinPayment\Livewire\Admin\Withdrawals;
use Hexters\CoinPayment\Livewire\FormTransaction;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CoinPayment Routes
|--------------------------------------------------------------------------
*/

Route::as('coinpayment.')
    ->prefix('coinpayment')
    ->group(function () {

        // Checkout page (full-page Livewire component).
        Route::middleware(config('coinpayment.middleware', ['web']))->group(function () {
            Route::get('/make/{payload}', FormTransaction::class)->name('make');
        });

        /**
         * IPN handler (server-to-server callback).
         * Exclude this route from CSRF verification in the host app.
         */
        Route::post('/ipn', IPNController::class)
            ->middleware('web')
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->name('ipn');
    });

/*
|--------------------------------------------------------------------------
| Admin panel (standalone, gate protected)
|--------------------------------------------------------------------------
*/

Route::as('coinpayment.admin.')
    ->prefix(config('coinpayment.admin.prefix', 'coinpayment/admin'))
    ->middleware(array_merge(
        (array) config('coinpayment.admin.middleware', ['web']),
        [CoinpaymentAuthMiddleware::class]
    ))
    ->group(function () {
        Route::get('/', Balances::class)->name('balances');
        Route::get('/withdrawals', Withdrawals::class)->name('withdrawals');
        Route::get('/transactions', Transactions::class)->name('transactions');
    });
