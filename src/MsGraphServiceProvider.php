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
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {

            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/msgraph.php' => config_path('msgraph.php'),
            ], 'config');

            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/database/migrations/create_ms_graph_tokens_table.php' => $this->app->databasePath()."/migrations/{$timestamp}_create_ms_graph_tokens_table.php",
            ], 'migrations');            
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
