<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Voice\JsonAuthorization\App\AuthorizableSetType;
use Voice\JsonAuthorization\App\CachedModels\CachedAuthorizableModel;
use Voice\JsonAuthorization\Authorization\AbsoluteRights;
use Voice\JsonAuthorization\Authorization\EloquentEvents;
use Voice\JsonAuthorization\Authorization\RuleParser;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/asseco-authorization.php', 'asseco-authorization');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->registerCachedModels();
        $this->registerAuthorizationClasses();
    }

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/Config/asseco-authorization.php' => config_path('asseco-authorization.php'),]);

        $override = Config::get('asseco-authorization.override_authorization');

        if (!$this->app->runningInConsole() && !$override) {
            $this->app->make(EloquentEvents::class)->attachEloquentListener();
        }
    }

    protected function registerCachedModels(): void
    {
        $this->app->singleton(CachedAuthorizableModel::class);
    }

    protected function registerAuthorizationClasses(): void
    {
        $this->app->singleton('cached-authorizable-set-types', static function ($app) {
            return AuthorizableSetType::getCached();
        });

        $this->app->singleton(AbsoluteRights::class);
        $this->app->singleton(RuleParser::class);
        $this->app->singleton(EloquentEvents::class);
    }
}
