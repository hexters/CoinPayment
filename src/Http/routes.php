<?php

Route::group([
  'middleware' => ['web', 'auth'],
  'prefix' => 'coinpayment',
  'namespace' => 'Hexters\CoinPayment\Http\Controllers'
],
function() {
    Route::get('/', function(){
      return abort(404);
    })->name('coinpayment.home');
    Route::get('/{serialize}', 'CoinPaymentController@index')->name('coinpayment.create.transaction');
    Route::get('/ajax/rates/{usd}', 'CoinPaymentController@ajax_rates')->name('coinpayment.ajax.rate.usd');
    Route::post('/ajax/maketransaction', 'CoinPaymentController@make_transaction')->name('coinpayment.ajax.store.transaction');
    Route::get('/ajax/trxinfo/{trxid}', 'CoinPaymentController@trx_info')->name('coinpayment.ajax.trxinfo');
    Route::get('/ajax/transaction/histories', 'CoinPaymentController@transactions_list_any')->name('coinpayment.ajax.transaction.histories');

    Route::get('/transactions/histories', 'CoinPaymentController@transactions_list')->name('coinpayment.transaction.histories');
});
