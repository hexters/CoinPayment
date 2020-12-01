<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
  
  public function env() {

    if(!env('APP_DEBUG')) {
      return is_null(ladmin()->get_option('ladmin-coinpayment-plugin-license'));
    }

    return false;
  }
  
  
}