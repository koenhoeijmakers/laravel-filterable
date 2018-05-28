<?php

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->packageRootPath('config/filterable.php') => config_path('filterable.php'),
        ]);

        $this->mergeConfigFrom(
            $this->packageRootPath('config/filterable.php'), 'filterable'
        );
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Filterable::class, function (Application $app) {
            return new Filterable($app['config'], $app['request']);
        });
    }

    /**
     * Get the package root path.
     *
     * @param $path
     * @return string
     */
    protected function packageRootPath($path)
    {
        return __DIR__ . '/../' . $path;
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string $path
     * @param  string $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, array_merge_recursive(require $path, $config));
    }
}
