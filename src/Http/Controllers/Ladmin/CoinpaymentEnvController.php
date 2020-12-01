<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use Illuminate\Http\Request;

class CoinpaymentEnvController extends Controller {
    
  public function __invoke(Request $request) {

    return response()->json([
      'result' => $this->env()
    ]);

  }

}
