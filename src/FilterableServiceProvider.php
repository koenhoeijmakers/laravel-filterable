<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }

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
        /** @var Repository $repository */
        $repository = $this->app->make(Repository::class);

        $config = $repository->get($key, []);

        $repository->set($key, $this->mergeConfig(require $path, $config));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array $original
     * @param  array $merging
     * @return array
     */
    protected function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($original, $merging);

        foreach ($original as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (! Arr::exists($merging, $key)) {
                continue;
            }

            if (is_numeric($key)) {
                continue;
            }

            $array[$key] = $this->mergeConfig($value, $merging[$key]);
        }

        return $array;
    }
}