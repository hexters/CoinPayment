<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::as('coinpayment.')->prefix('coinpayment')
    ->group(function() {
        
        Route::middleware(['web'])->group(function() {
            /**
             * Transaction created
             */
            Route::resource('/make', 'MakeTransactionController')->only(['show', 'store']);

            /**
             * Ajax section
             */
            Route::group([
                'prefix' => 'ajax',
            ], function() {
                Route::get('/rates/{usd}', 'AjaxController@rates')->name('rates');
                Route::post('/payload', 'AjaxController@encrypt_payload')->name('encrypt.payload');
                Route::post('/create', 'AjaxController@create_transaction')->name('create.transaction');
            });
        });

        /**
         * IPN handler
         * Please except into csrf proccess /coinpayment/ipn
         */
        Route::post('/ipn', 'IPNController')->name('ipn');
    });
