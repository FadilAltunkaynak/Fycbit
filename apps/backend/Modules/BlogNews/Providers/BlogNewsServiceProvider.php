<?php

namespace Modules\BlogNews\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Modules\BlogNews\Console\SlugChange;
use Illuminate\Database\Eloquent\Factory;
use Modules\BlogNews\Http\Middleware\CheckModuleBlogNews;

require(__DIR__ . '/../Helper/helper.php');

class BlogNewsServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        // if (!is_dir(public_path(PUBLIC_LINK))) {
        //     shell_exec("cd storage && mkdir blog_news && cd blog_news && mkdir blog_filemanager && mkdir thumbs");
        //     shell_exec("ln -s ../../../../Modules/BlogNews/Assets " . PUBLIC_LINK);
        // }

        $publicPath = public_path(PUBLIC_LINK);
        if (!is_dir($publicPath)) {
            $storagePath = storage_path('blog_news/blog_filemanager');
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true); // Recursively create directories
                mkdir(storage_path('blog_news/thumbs'), 0755, true); // Create thumbs directory
            }

            if (!is_link(base_path(PUBLIC_LINK))) {
                symlink(base_path('Modules/BlogNews/Assets'), base_path(PUBLIC_LINK));
            }
        }

        // $publicPath = public_path(PUBLIC_LINK);
        // if (!is_dir($publicPath)) {
        //     $storagePath = storage_path('blog_news/blog_filemanager');
        //     if (!is_dir($storagePath)) {
        //         mkdir($storagePath, 0755, true); // Recursively create directories
        //         mkdir(storage_path('blog_news/thumbs'), 0755, true); // Create thumbs directory
        //     }

        //     if (!file_exists($publicPath)) {
        //         symlink(base_path('Modules/BlogNews/Assets'), PUBLIC_LINK);
        //     }
        // }

        // if(!is_dir(public_path(PUBLIC_BLOG_FILEMANEGER))){
        //     shell_exec("mkdir ".PUBLIC_BLOG_FILEMANEGER);
        // }
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        app()->make('router')->aliasMiddleware('check_addon', CheckModuleBlogNews::class);
        Collection::macro('toSlugValue', function () {
            $data = [];
            $this->map(function ($value) use (&$data) {
                $data[$value->slug] = $value->value;
            });
            return (object) $data;
        });
        $this->commands([SlugChange::class]);
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
            __DIR__ . '/../Config/config.php' => config_path('blognews.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            'blognews'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/blognews');

        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/blognews';
        }, \Config::get('view.paths')), [$sourcePath]), 'blognews');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/blognews');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'blognews');
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'blognews');
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
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
