<?php

namespace Szhorvath\OperaSalesforce;

use Illuminate\Support\ServiceProvider;

class OperaSalesforceServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'szhorvath');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'szhorvath');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/opera_salesforce.php', 'opera_salesforce');

        // Register the service the package provides.
        $this->app->singleton('opera-salesforce', function ($app) {

            $defaultRegion = config('opera_salesforce.default');
            $regions = config('opera_salesforce.regions');

            return new OperaSalesforce($regions[$defaultRegion]);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['opera-salesforce'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/opera-salesforce.php' => config_path('opera-salesforce.php'),
        ], 'opera-salesforce.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/szhorvath'),
        ], 'opera-salseforce.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/szhorvath'),
        ], 'opera-salseforce.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/szhorvath'),
        ], 'opera-salseforce.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
