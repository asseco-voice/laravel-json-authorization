<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization;

use Asseco\JsonAuthorization\App\Console\Commands\SyncAuthorizableModels;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
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

        if (config('asseco-authorization.migrations.run')) {
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
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

        $this->app->bind(AuthorizableModel::class, config('asseco-authorization.models.authorizable_model'));
        $this->app->bind(AuthorizableSetType::class, config('asseco-authorization.models.authorizable_set_type'));
        $this->app->bind(AuthorizationRule::class, config('asseco-authorization.models.authorization_rule'));

        $this->registerAuthorizationClasses();

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
        $this->app->singleton(AbsoluteRights::class);
        $this->app->singleton(RuleParser::class);
        $this->app->singleton(EloquentEvents::class);
    }
}
