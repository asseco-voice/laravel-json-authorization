<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Voice\JsonAuthorization\Authorization\AbsoluteRights;
use Voice\JsonAuthorization\Authorization\EloquentEvents;
use Voice\JsonAuthorization\Authorization\RuleParser;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asseco-authorization.php', 'asseco-authorization');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->registerAuthorizationClasses();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/asseco-authorization.php' => config_path('asseco-authorization.php')]);

        $override = Config::get('asseco-authorization.override_authorization');

        if (!$this->app->runningInConsole() && !$override) {
            /**
             * @var EloquentEvents $eloquentEvents
             */
            $eloquentEvents = $this->app->make(EloquentEvents::class);
            $eloquentEvents->attachEloquentListener();
        }
    }

    protected function registerAuthorizationClasses(): void
    {
        $this->app->singleton(AbsoluteRights::class);
        $this->app->singleton(RuleParser::class);
        $this->app->singleton(EloquentEvents::class);
    }
}
