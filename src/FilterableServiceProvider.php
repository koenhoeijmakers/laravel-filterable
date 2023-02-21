<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Support\ServiceProvider;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering as FilteringContract;

class FilterableServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register():void
    {
        $this->app->bind(FilteringContract::class, Filtering::class);
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot():void
    {
        $this->publishes([
            $this->packageRootPath('config/filterable.php') => config_path('filterable.php'),
        ]);

        $this->mergeConfigFrom(
            $this->packageRootPath('config/filterable.php'),
            'filterable'
        );
    }

    /**
     * Get the package root path.
     *
     * @param $path
     * @return string
     */
    protected function packageRootPath($path):string
    {
        return __DIR__ . '/../' . $path;
    }
}
