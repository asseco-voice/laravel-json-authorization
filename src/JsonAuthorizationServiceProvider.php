<?php

namespace Voice\JsonAuthorization;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Voice\JsonAuthorization\Authorization\AuthenticatedUser;
use Voice\JsonAuthorization\Authorization\AuthorizableModels;
use Voice\JsonAuthorization\Authorization\EloquentEvents;
use Voice\JsonAuthorization\Authorization\RightParser;
use Voice\JsonAuthorization\Authorization\RulesResolver;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/asseco-authorization.php', 'asseco-authorization');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->registerAuthorizationClasses();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/Config/asseco-authorization.php' => config_path('asseco-authorization.php'),]);

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
        $this->app->singleton(AuthenticatedUser::class, function ($app) {
            return new AuthenticatedUser();
        });

        $this->app->singleton(RightParser::class, function ($app) {
            return new RightParser(new AuthenticatedUser(), new AuthorizableModels(), new RulesResolver());
        });

        $this->app->singleton(EloquentEvents::class, function ($app) {
            return new EloquentEvents();
        });
    }
}
