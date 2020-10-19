<?php

namespace Hexters\CoinPayment\Providers;

use Illuminate\Support\ServiceProvider;
use Hexters\CoinPayment\Console\InstallationCommand;
use Hexters\CoinPayment\Helpers\CoinPaymentHelper;

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
    public function boot() {
        $this->registerCommand();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->bind('CoinPayment', function(){
            return new CoinPaymentHelper;
        });
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig() {

        
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('coinpayment.php'),
            /**
             * Publishing assets
             */
            __DIR__.'/../Resources/assets/prod/css/coinpayment.css' => public_path('css/coinpayment.css'),
            __DIR__.'/../Resources/assets/prod/js/coinpayment.js' => public_path('js/coinpayment.js'),
            __DIR__.'/../Resources/assets/images' => public_path('/'),
            /**
             * Publishing Jobs
             *
             */
            __DIR__.'/../Jobs/CoinpaymentListener.php' => app_path('jobs/CoinpaymentListener.php'),
        ], 'coinpayment');

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
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function registerCommand () {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallationCommand::class
            ]);
        }
    }

}
