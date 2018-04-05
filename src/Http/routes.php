<?php

Route::group([
  'middleware' => [
    'web',
    'auth',
    Hexters\CoinPayment\Http\Middleware\listenConfigFileMiddleware::class
  ],
  'prefix' => 'coinpayment',
  'namespace' => 'Hexters\CoinPayment\Http\Controllers'
],
function() {
    Route::get('/', function(){
      return abort(404);
    })->name('coinpayment.home');
    Route::get('/{serialize}', 'CoinPaymentController@index')->name('coinpayment.create.transaction');
    Route::get('/ajax/rates/{usd}', 'CoinPaymentController@ajax_rates')->name('coinpayment.ajax.rate.usd');
    Route::get('/ajax/transaction/histories', 'CoinPaymentController@transactions_list_any')->name('coinpayment.ajax.transaction.histories');
    Route::post('/ajax/maketransaction', 'CoinPaymentController@make_transaction')->name('coinpayment.ajax.store.transaction');
    Route::post('/ajax/trxinfo', 'CoinPaymentController@trx_info')->name('coinpayment.ajax.trxinfo');
    Route::post('/ajax/transaction/manual/check', 'CoinPaymentController@manual_check')->name('coinpayment.ajax.transaction.manual.check');

    Route::get('/transactions/histories', 'CoinPaymentController@transactions_list')->name('coinpayment.transaction.histories');
});

Route::group([
    'namespace' => 'Hexters\CoinPayment\Http\Controllers'
], function(){
  Route::post('/coinpayment/ipn', 'CoinPaymentController@receive_webhook')
    ->middleware('web')
    ->name('coinpayment.ipn.received');
});
