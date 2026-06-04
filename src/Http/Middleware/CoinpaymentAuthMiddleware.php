<?php

namespace Hexters\CoinPayment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CoinpaymentAuthMiddleware
{
    /**
     * Authenticate and authorize access to the CoinPayment admin panel.
     *
     * Guests are redirected to a configurable target
     * (config("coinpayment.admin.redirect") — a route name or URL; falls back
     * to the app's "login" route). Authenticated users must then pass the
     * gate; it fails closed when the gate is configured but undefined.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $this->redirectGuest($request);
        }

        $gate = config('coinpayment.admin.gate');

        if (! empty($gate) && (! Gate::has($gate) || Gate::denies($gate))) {
            abort(403, 'Unauthorized access to CoinPayment admin.');
        }

        return $next($request);
    }

    /**
     * Send an unauthenticated visitor to the configured login target.
     */
    protected function redirectGuest(Request $request): Response
    {
        if ($request->expectsJson()) {
            abort(401, 'Unauthenticated.');
        }

        $target = config('coinpayment.admin.redirect');

        if (! empty($target)) {
            return redirect()->guest(Route::has($target) ? route($target) : $target);
        }

        if (Route::has('login')) {
            return redirect()->guest(route('login'));
        }

        abort(401, 'Unauthenticated.');
    }
}
