<?php

namespace Dcblogdev\MsGraph;

use Dcblogdev\MsGraph\Console\Commands\MsGraphAdminKeepAliveCommand;
use Dcblogdev\MsGraph\Console\Commands\MsGraphKeepAliveCommand;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class MsGraphServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->registerMiddleware($router);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->configurePublishing();
        }
    }

    public function registerMiddleware($router)
    {
        $router->aliasMiddleware('MsGraphAuthenticated', MsGraphAuthenticated::class);
        $router->aliasMiddleware('MsGraphAdminAuthenticated', MsGraphAdminAuthenticated::class);
    }

    public function registerCommands()
    {
        $this->commands([
            MsGraphAdminKeepAliveCommand::class,
            MsGraphKeepAliveCommand::class,
        ]);
    }

    public function configurePublishing()
    {
        $this->publishes([
            __DIR__.'/../config/msgraph.php' => config_path('msgraph.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/Listeners/NewMicrosoft365SignInListener.php' => app_path('Listeners/NewMicrosoft365SignInListener.php'),
        ], 'Listeners');

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/database/migrations/create_ms_graph_tokens_table.php' => $this->app->databasePath()."/migrations/{$timestamp}_create_ms_graph_tokens_table.php",
        ], 'migrations');
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

        $this->app->singleton('msgraphadmin', function ($app) {
            return new MsGraphAdmin;
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
