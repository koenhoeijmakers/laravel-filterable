# Laravel Filterable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-filterable)

A Laravel package to implement filtering by request parameters.
```php
example.com/json?name=Koen&sortBy=name&desc=0
```

## Usage
Require the package.
```sh
composer require koenhoeijmakers/laravel-filterable
```

Inject it in your controller (or resolve it from the container in any other way).
```php

namespace App\Http\Controllers\Api\User;

use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering;

final class Index
{
    public function __construct(
        private readonly Filtering $filtering
    ) {}

    public function __invoke()
    {
        $builder = User::query();
        
        $this->filtering->builder($builder)
            ->filterFor('name', fn(Builder $builder, string $value) => $builder
                ->where('name', 'like', "{$value}%");
            )
            ->sortFor('name')
            ->defaultSorting('name')
            ->filter();
    
        return UserResource::collection($builder->paginate());
    }
}
```
