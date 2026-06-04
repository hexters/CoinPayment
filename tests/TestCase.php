<?php

namespace Hexters\CoinPayment\Tests;

use Hexters\CoinPayment\Providers\CoinPaymentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            CoinPaymentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('coinpayment.public_key', 'test-public');
        $app['config']->set('coinpayment.private_key', 'test-private');
        $app['config']->set('coinpayment.default_currency', 'USD');
        $app['config']->set('coinpayment.listener', null);
        $app['config']->set('coinpayment.ipn.config.coinpayment_ipn_secret', 'ipn-secret');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/Database/Migrations');
    }
}
