<?php

namespace GetCandy\ScoutDatabaseEngine;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class ScoutDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        app(EngineManager::class)->extend('database_index', function () {
            return new DatabaseEngine;
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
