<?php

namespace Hexters\CoinPayment\Traits;

trait InteractsWithListener
{
    /**
     * Dispatch the configured transaction listener job, if it exists.
     *
     * The listener class is resolved from config("coinpayment.listener")
     * and defaults to the job published into the host app. When the class
     * is missing the call is a no-op so the package never hard-fails on a
     * fresh install.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchListener(array $payload): void
    {
        $listener = config('coinpayment.listener', \App\Jobs\CoinpaymentListener::class);

        if (is_string($listener) && class_exists($listener)) {
            dispatch(new $listener($payload));
        }
    }
}
