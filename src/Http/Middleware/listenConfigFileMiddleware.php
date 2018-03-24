<?php
namespace Hexters\CoinPayment\Http\Middleware;

use Closure;

class listenConfigFileMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
      if(empty(config('coinpayment.public_key')) || empty(config('coinpayment.private_key'))){
        return response('Oops! look like shometing wrong, please visit <a href="https://www.coinpayments.net/" target="_blank">https://www.coinpayments.net</a> for getting API token. And add variable
        <br>
          <code>
            COIN_PAYMENT_PUBLIC_KEY=your_public_api_token<br>
            COIN_PAYMENT_PRIVATE_KEY=your_secret_api_token
          </code>
          <br> in file <code>.env</code>', 401);
      }

      return $next($request);
    }
}
