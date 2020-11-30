<?php 

namespace Hexters\CoinPayment;

use Illuminate\Support\Facades\Route;
use Hexters\CoinPayment\Http\Controllers\Ladmin\CoinPaymentTransactionController;

class CoinPaymentPlugin {

  public static function route() {
    Route::resource('/coinpayment', CoinPaymentTransactionController::class);

  }

  public static function menus() {
    return [
      'gate' => 'administrator.coinpayment.index',
      'name' => 'Coinpayment',
      'description' => 'List of coinpayment transation',
      'route' => ['administrator.coinpayment.index', []],
      'isActive' => 'coinpayment*',
      'icon' => 'bitcoin',
      'id' => '',
      'gates' => [
        [
          'gate' => 'administrator.coinpayment.show',
          'title' => 'Details of transaction',
          'description' => 'User can view details of transaction'
        ],
      ]
    ];
  }

}