<?php

namespace Hexters\CoinPayment\Providers;

use Hexters\CoinPayment\Console\InstallationCommand;
use Hexters\CoinPayment\Console\SyncTransactionsCommand;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;
use Hexters\CoinPayment\Livewire\Admin\Balances;
use Hexters\CoinPayment\Livewire\Admin\Transactions;
use Hexters\CoinPayment\Livewire\Admin\Withdrawals;
use Hexters\CoinPayment\Livewire\FormTransaction;
use Hexters\CoinPayment\Livewire\LicenseGate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CoinPaymentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'coinpayment');

        $this->app->singleton('CoinPayment', fn () => new CoinPaymentHelper);
    }

    /**
     * Boot the package services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerTranslations();
        $this->registerViews();
        $this->registerRoutes();
        $this->registerLivewireComponents();
        $this->registerPublishing();

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('coinpayment-form-transaction', FormTransaction::class);
        Livewire::component('coinpayment-license-gate', LicenseGate::class);
        Livewire::component('coinpayment-admin-balances', Balances::class);
        Livewire::component('coinpayment-admin-withdrawals', Withdrawals::class);
        Livewire::component('coinpayment-admin-transactions', Transactions::class);
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'coinpayment');
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'coinpayment');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallationCommand::class,
                SyncTransactionsCommand::class,
            ]);
        }
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('coinpayment.php'),
        ], 'coinpayment-config');

        $this->publishes([
            __DIR__ . '/../Resources/assets/prod/css/coinpayment.css' => public_path('vendor/coinpayment/coinpayment.css'),
            __DIR__ . '/../Resources/assets/images' => public_path('vendor/coinpayment'),
        ], 'coinpayment-assets');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/coinpayment'),
        ], 'coinpayment-views');

        $this->publishes([
            __DIR__ . '/../Jobs/CoinpaymentListener.php' => app_path('Jobs/CoinpaymentListener.php'),
        ], 'coinpayment-job');
    }
}
