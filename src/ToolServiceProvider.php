<?php

namespace Visanduma\NovaSettings;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;
use Visanduma\NovaSettings\Console\Commands\CreateSettings;
use Visanduma\NovaSettings\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        $this->commands([
            CreateSettings::class,
        ]);

        $this->publishes([
            __DIR__.'/nova-settings.php' => config_path('nova-settings.php'),
            __DIR__.'/../database/create_advance_nova_settings_table.php' => database_path('/migrations/'.'2024_06_13_000000_create_advance_nova_settings_table.php'),
        ], 'nova-settings');

        NovaSettings::autoRegister();

    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authenticate::class, Authorize::class], 'nova-settings')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/nova-settings')
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/nova-settings.php', 'nova-settings');

    }
}
