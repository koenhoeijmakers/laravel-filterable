<?php

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class FilterableServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind(Filterable::class, function (Application $app) {
            return new Filterable($app['config'], $app['request']);
        });
    }
}
