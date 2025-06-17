<?php

namespace Dcblogdev\MsGraph;

use Dcblogdev\MsGraph\Console\Commands\MsGraphAdminKeepAliveCommand;
use Dcblogdev\MsGraph\Console\Commands\MsGraphKeepAliveCommand;
use Dcblogdev\MsGraph\Facades\MsGraph as MsGraphFacade;
use GuzzleHttp\Client;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Microsoft\Graph\Graph;
use ShitwareLtd\FlysystemMsGraph\Adapter;

class MsGraphServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $this->registerCommands();
        $this->registerMiddleware($router);
        $this->configurePublishing();
        $this->registerFilesystem();
    }

    public function registerMiddleware(Router $router): void
    {
        $router->aliasMiddleware('MsGraphAuthenticated', MsGraphAuthenticated::class);
        $router->aliasMiddleware('MsGraphAdminAuthenticated', MsGraphAdminAuthenticated::class);
    }

    public function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MsGraphAdminKeepAliveCommand::class,
            MsGraphKeepAliveCommand::class,
        ]);
    }

    public function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

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

    public function registerFilesystem(): void
    {
        Storage::extend('msgraph', function (Application $app, array $config) {
            $graph = new Graph;

            if (MsGraphFacade::isConnected()) {
                $graph = $graph->setAccessToken(MsGraphFacade::getAccessToken());
            } else {
                $tenantId = config('msgraph.tenantId');
                $clientId = config('msgraph.clientId');
                $clientSecret = config('msgraph.clientSecret');

                $guzzle = new Client;
                $response = $guzzle->post("https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token",
                    [
                        'headers' => [
                            'Host' => 'login.microsoftonline.com',
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                        'form_params' => [
                            'client_id' => $clientId,
                            'scope' => 'https://graph.microsoft.com/.default',
                            'client_secret' => $clientSecret,
                            'grant_type' => 'client_credentials',
                        ],
                    ]);
                $body = json_decode($response->getBody()->getContents());
                $graph = $graph->setAccessToken($body->access_token);
            }

            $adapter = new Adapter($graph, $config['driveId']);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config), $adapter, $config,
            );
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/msgraph.php', 'msgraph');

        // Register the service the package provides.
        $this->app->singleton('msgraph', function () {
            return new MsGraph;
        });

        $this->app->singleton('msgraphadmin', function () {
            return new MsGraphAdmin;
        });
    }

    public function provides(): array
    {
        return ['msgraph'];
    }
}
