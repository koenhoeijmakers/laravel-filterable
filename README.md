# Laravel Filterable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-filterable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)

A laravel package to implement filtering by request variables.

## Usage
Require the package.
```sh
composer require koenhoeijmakers/laravel-filterable
```

### Acquiring an instance.
Inject it in your controller.
```php
namespace App\Http\Controllers;

use KoenHoeijmakers\LaravelFilterable\Filterable;

class ProductController extends Controller
{
    public function json(Filterable $filterable)
    {
        //
    }
}
```

... or resolve it from the container.
```php
$filterable = app(Filterable::class);
```

### Passing the subject
Pass a model reference.
```php
$filterable->model(Product::class);
```

... or pass a query builder.
```php
$query = Product::query();

$filterable->query($query);
```

### Registering filters
You can register simple filters like `equal` and `like`.
```php
$filterable->registerFilter('name', 'like');
```

... or create a custom filter.
```php
namespace App\Filters;

use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter;

class CustomFilter implements Filter
{
    public function __invoke(Builder $builder, string $column, $value)
    {
        return $builder->whereHas('prices', function (Builder $builder) use ($value) {
            $builder->where('price', '=', $value);
        });
    }
}
```
In this custom class `$column` is the columm you've assigned it to (in our case `name`)
and value is the value gotten from the request.

```php
use App\Filters\CustomFilter;

$filterable->registerFilter('name', CustomFilter::class);
```

### Registering sorters
Which work pretty much the same as filters.
```php
$filterable->registerSorter('name', 'order_by');
```

### Multiple filters / sorters
You can of course register multiple filters / sorters at once.
```php
$filterable->registerFilters([
    'name'        => 'like',
    'description' => CustomFilter::class,
]);

$filterable->registerSorters([
    'name'     => 'order_by',
    'relation' => SuperCustomRelationFilter::class
]);
```

### Executing the filters and orders
After you've registered the filters and orders you may call the following to execute them.
```php
$filterable->filter();
```

This returns an eloquent query builder, and thus you're free to use `->get()` or `->paginate()` or whatever your needs may be.

### Adding advanced querying
You might want to add some extra wheres or implement a scope for a `Filterable` instance.

For this you can do the following.
```php
$query = $filterable->getBuilder();

$query->where(...)->someScope(...);
```
