<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CoinpaymentEnvController extends Controller {
    
  public function __invoke(Request $request) {
    
    $paymentStatus = Cache::get('hexters-coinpayment-unpaid');
    
      $haspayment = false;
      $invoice = null;
      $link = null;
      $activation = null;

    if(! is_null($paymentStatus)) {
      $haspayment = true;
      $invoice = $paymentStatus['invoice'];
      $link = $paymentStatus['link'];
      $activation = $paymentStatus['activation'];
    }

    return response()->json([
      'haspayment' => $haspayment,
      'invoice' => $invoice,
      'link' => $link,
      'activation' => $activation,
      'result' => $this->env()
    ]);

  }

}
