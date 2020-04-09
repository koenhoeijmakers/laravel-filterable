# Laravel Filterable
[![Build Status](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/build.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-filterable)

A laravel package to implement filtering by request parameters.
```php
example.com/json?name=Koen
```

## Usage
Require the package.
```sh
composer require koenhoeijmakers/laravel-filterable
```

Inject it in your controller (or resolve it from the container in any other way).
```php

namespace App\Http\Controllers\User;

use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering;

class Index
{
    protected $filtering;

    public function __construct(Filtering $filtering)
    {
        $this->filtering = $filtering;
    }

    public function __invoke()
    {
        $builder = User::query();
        
        $this->filtering->builder($builder)
            ->filterFor('name', fn(Builder $builder) => $builder
                ->where('name', 'like', $value . '%');
            })
            ->sortFor('name')
            ->filter();
    
        return UserResource::collection($builder->paginate());
    }
}
```
