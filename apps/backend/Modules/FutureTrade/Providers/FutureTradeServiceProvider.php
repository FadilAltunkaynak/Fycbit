<?php

namespace Modules\FutureTrade\Providers;
require_once __DIR__. '/../Helpers/helpers.php';

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\FutureTrade\Console\FutureBotCleanupCommand;
use Modules\FutureTrade\Console\FutureBotRunnerCommand;
use Modules\FutureTrade\Console\PositionProcess;
use Modules\FutureTrade\Services\ProviderServices\ProviderService;

class FutureTradeServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        require __DIR__. '/../Routes/channels.php';

        // service provider settings
        ProviderService::set_dependence($this->app);
        ProviderService::setObservers();

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->commands([
            PositionProcess::class,
            FutureBotRunnerCommand::class,
            FutureBotCleanupCommand::class,
        ]);

 
        // if ($this->app->runningInConsole()) {
        //     $this->app->booted(function () {
        //         $schedule = $this->app->make(Schedule::class);
        //         $schedule->command('future-position')->everyMinute();
        //     });
        // }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        ProviderService::addRedisConfig();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('futuretrade.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'futuretrade'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/futuretrade');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/futuretrade';
        }, \Config::get('view.paths')), [$sourcePath]), 'futureTrade');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/futuretrade');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'futuretrade');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'futuretrade');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
