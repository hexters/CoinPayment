<?php 

namespace Hexters\CoinPayment;

use Illuminate\Support\Facades\Route;
use Hexters\CoinPayment\Http\Controllers\Ladmin\CoinPaymentTransactionController;
use Hexters\CoinPayment\Http\Controllers\Ladmin\CoinpaymentBalanceController;
use Hexters\CoinPayment\Http\Controllers\Ladmin\CoinPaymentWithdrawalController;
use Hexters\CoinPayment\Http\Controllers\Ladmin\CoinpaymentEnvController;
use Hexters\CoinPayment\Http\Controllers\Ladmin\AjaxController;





class CoinPaymentPlugin {

  public static function route() {

    Route::group([ 'as' => 'coinpayment.', 'prefix' => 'coinpayment' ], function() {
      Route::resource('/transaction', CoinPaymentTransactionController::class)->except(['edit']);
      Route::resource('/balances', CoinpaymentBalanceController::class)->only(['index']);
      
      Route::prefix('ajax')->as('ajax.')->group(function() {
        Route::post('/environment', CoinpaymentEnvController::class)->name('llc');
        Route::get('/balances', [AjaxController::class, 'get_balance'])->name('balances');
        Route::post('/top_up', [AjaxController::class, 'top_up'])->name('top_up');
      });
    });


  }

  public static function menus() {
    return [
      'gate' => 'administrator.coinpayment',
      'name' => 'Coinpayment',
      'description' => 'Coinpayment Menus',
      'route' => null,
      'isActive' => null,
      'icon' => 'bitcoin',
      'id' => '',
      'gates' => [],
      'submenus' => [
        [
          'gate' => 'administrator.coinpayment.transaction.index',
          'name' => 'Transaction',
          'description' => 'List of coinpayment transation',
          'route' => ['administrator.coinpayment.transaction.index', []],
          'isActive' => 'coinpayment/transaction*',
          'id' => '',
          'gates' => [
            [
              'gate' => 'administrator.coinpayment.transaction.create',
              'title' => 'Create transaction',
              'description' => 'User can create new transaction'
            ],
            [
              'gate' => 'administrator.coinpayment.transaction.show',
              'title' => 'Details of transaction',
              'description' => 'User can view details of transaction'
            ],

            [
              'gate' => 'administrator.coinpayment.transaction.cehck.status',
              'title' => 'Check status',
              'description' => 'User Check status transaction'
            ],
          ],
        ],

        [
          'gate' => 'administrator.coinpayment.balances.index',
          'name' => 'Balances',
          'description' => 'Details of balances',
          'route' => ['administrator.coinpayment.balances.index', []],
          'isActive' => 'coinpayment/balances*',
          'id' => '',
          'gates' => [
            [
              'gate' => 'administrator.coinpayment.balances.topup',
              'title' => 'Create Top Up',
              'description' => 'User can create top up'
            ],
          ],
        ],
      ]
    ];
  }

}