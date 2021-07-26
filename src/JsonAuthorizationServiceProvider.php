<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization;

use Asseco\JsonAuthorization\App\Console\Commands\SyncAuthorizableModels;
use Asseco\JsonAuthorization\Authorization\AbsoluteRights;
use Asseco\JsonAuthorization\Authorization\EloquentEvents;
use Asseco\JsonAuthorization\Authorization\RuleParser;
use Illuminate\Support\ServiceProvider;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asseco-authorization.php', 'asseco-authorization');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        if (config('asseco-authorization.runs_migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }

        $this->registerAuthorizationClasses();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../migrations' => database_path('migrations'),
        ], 'asseco-authorization');

        $this->publishes([
            __DIR__ . '/../config/asseco-authorization.php' => config_path('asseco-authorization.php'),
        ], 'asseco-authorization');
        
        $override = config('asseco-authorization.override_authorization');

        if (!app()->runningInConsole() && !$override) {
            /**
             * @var EloquentEvents $eloquentEvents
             */
            $eloquentEvents = app()->make(EloquentEvents::class);
            $eloquentEvents->attachEloquentListener();
        }

        $this->commands([
            SyncAuthorizableModels::class,
        ]);
    }

    protected function registerAuthorizationClasses(): void
    {
        app()->singleton(AbsoluteRights::class);
        app()->singleton(RuleParser::class);
        app()->singleton(EloquentEvents::class);
    }
}
