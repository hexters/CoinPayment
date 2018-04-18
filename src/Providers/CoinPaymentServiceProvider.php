<?php

namespace Hexters\CoinPayment\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Hexters\CoinPayment\Classes\CoinPaymentClass;

use Hexters\CoinPayment\Console\chekcingTransactionCommand;
use Hexters\CoinPayment\Console\EnabledIPNCommand;

class CoinPaymentServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');


        if ($this->app->runningInConsole()) {
            $this->commands([
                chekcingTransactionCommand::class,
                EnabledIPNCommand::class
            ]);
        }

        if(!is_dir(app_path('/Jobs')))
          mkdir(app_path('/Jobs'));

        $this->publishes([
            __DIR__ . '/../Config/coinpayment.php' => config_path('coinpayment.php'),
            __DIR__ . '/../Assets/images/coinpayment.logo.png' => public_path('/coinpayment.logo.png'),
            __DIR__ . '/../Jobs/coinPaymentCallbackProccedJob.php' => app_path('/Jobs/coinPaymentCallbackProccedJob.php'),
            __DIR__ . '/../Jobs/IPNHandlerCoinPaymentJob.php' => app_path('/Jobs/IPNHandlerCoinPaymentJob.php')
        ], 'coinpayment-publish');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->bind('CoinPayment', function(){
          return new CoinPaymentClass;
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('coinpayment.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'coinpayment'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/coinpayment');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/coinpayment';
        }, \Config::get('view.paths')), [$sourcePath]), 'coinpayment');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/coinpayment');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'coinpayment');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'coinpayment');
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
