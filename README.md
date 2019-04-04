# Laravel Filterable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-filterable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)

A laravel package to implement filtering by request parameters.
```php
example.com/json?q[name]=Koen
```

## Usage
Require the package.
```sh
composer require koenhoeijmakers/laravel-filterable
```

### Acquiring an instance.
Inject it in your controller.
```php

namespace App\Http\Controllers\User;

use KoenHoeijmakers\LaravelFilterable\Filtering;

class Index extends Controller
{
    public function json(Filtering $filtering)
    {
        //
    }
}
```
