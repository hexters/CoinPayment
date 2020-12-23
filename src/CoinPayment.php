<?php
namespace Hexters\CoinPayment;

use Illuminate\Support\Facades\Facade;

class CoinPayment extends Facade {
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'CoinPayment'; }
}