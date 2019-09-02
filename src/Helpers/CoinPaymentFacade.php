<?php
namespace Hexters\CoinPayment\Helpers;

use Illuminate\Support\Facades\Facade;

class CoinPaymentFacade extends Facade {
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor() { return 'CoinPayment'; }
}