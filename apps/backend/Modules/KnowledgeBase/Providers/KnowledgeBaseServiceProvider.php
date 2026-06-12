<?php

namespace Modules\KnowledgeBase\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Pagination\Paginator;
use Modules\KnowledgeBase\Http\Middleware\KnowledgeBaseOffMiddleware;

require(__DIR__ . '/../Helper/helper.php');
class KnowledgeBaseServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(module_path('KnowledgeBase', 'Database/Migrations'));
        app()->make('router')->aliasMiddleware('user_auth', \Modules\KnowledgeBase\Http\Middleware\UserAuthMiddleware::class);
        app()->make('router')->aliasMiddleware('check_knowledgebase_module_status', \Modules\KnowledgeBase\Http\Middleware\CheckKnowledgeBaseModuleStatusMiddleware::class);
        app()->make('router')->aliasMiddleware('off_knowledgebase',KnowledgeBaseOffMiddleware::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\KnowledgeBase\Console\CopyDirectoryInPublicCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('knowledgebase.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'knowledgebase'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/knowledgebase');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/knowledgebase';
        }, \Config::get('view.paths')), [$sourcePath]), 'knowledgebase');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/knowledgebase');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'knowledgebase');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'knowledgebase');
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
