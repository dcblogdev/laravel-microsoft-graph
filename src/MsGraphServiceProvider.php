<?php

namespace DaveismynameLaravel\MsGraph;

use Illuminate\Support\ServiceProvider;

class MsGraphServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'daveismynamelaravel');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'daveismynamelaravel');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {

            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/msgraph.php' => config_path('msgraph.php'),
            ], 'msgraph.config');

            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_ms_graph_tokens_tables.php.stub' => $this->app->databasePath()."/migrations/{$timestamp}_create_ms_graph_tokens_tables.php",
            ], 'migrations');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/daveismynamelaravel'),
            ], 'msgraph.views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/daveismynamelaravel'),
            ], 'msgraph.views');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/daveismynamelaravel'),
            ], 'msgraph.views');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/msgraph.php', 'msgraph');

        // Register the service the package provides.
        $this->app->singleton('msgraph', function ($app) {
            return new MsGraph;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['msgraph'];
    }
}
