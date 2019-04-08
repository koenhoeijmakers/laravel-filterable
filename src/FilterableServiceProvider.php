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
}
