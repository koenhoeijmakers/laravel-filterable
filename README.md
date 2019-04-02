# Laravel Filterable
[![Packagist](https://img.shields.io/packagist/v/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/koenhoeijmakers/laravel-filterable/?branch=master)
[![license](https://img.shields.io/github/license/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://github.com/koenhoeijmakers/laravel-filterable)
[![Packagist](https://img.shields.io/packagist/dt/koenhoeijmakers/laravel-filterable.svg?colorB=brightgreen)](https://packagist.org/packages/koenhoeijmakers/laravel-filterable)

A laravel package to implement filtering by request variables onto a query builder.
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
namespace App\Http\Controllers;

use KoenHoeijmakers\LaravelFilterable\Filtering;

class ProductController extends Controller
{
    public function json(Filtering $filtering)
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

use KoenHoeijmakers\LaravelFilterable\Filters\AbstractFilter;

class CustomFilter extends AbstractFilter
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @param string $column
     */
    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param  mixed                                $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $builder, $value)
    {
        return $builder->where($this->column, '=', $value);
    }
}
```

In this custom class `$column` is the columm you've assigned it to (in our case `name`) or what you have defined it for `new CustomFilter('not_name')`,
and `$value` is the value gotten from the request.

So to use the `name` key from the request you can do:
```php
use App\Filters\CustomFilter;

$filterable->registerFilter('name', CustomFilter::class);
```

... and if you'd like to override this key you can do:
```php
use App\Filters\CustomFilter;

$filterable->registerFilter('name', new CustomFilter('title'));
```

### Registering sorters
Which work pretty much the same as filters.
```php
$filterable->registerSorter('name', 'order_by');
```

### Multiple filters
You can of course register multiple filters at once.
```php
$filterable->registerFilters([
    'name'        => 'like',
    'description' => CustomFilter::class,
    'title'       => new TitleFilterOnDifferentColumn('not_the_title'),
]);
```

### Multiple sorters
You can of course register multiple sorters at once.
```php
$filterable->registerSorters([
    'name'     => 'order_by',
    'relation' => CustomSorter::class,
    'title'    => new TitleSorterOnDifferentColumn('not_the_title'),
]);
```

### Executing the filters and sorters
After you've registered the filters and orders you may call the following to execute them.
```php
$filterable->filter();
```

This returns an eloquent query builder, and thus you're free to use `->get()` or `->paginate()` or whatever your needs may be.

### Disabling default filtering and sorting
You can disable the "default" filtering or sorting, so that only registered filters or sorters will be queried.

This can be done globally from the config.
```php
return [
    //...
    'use_default_filter' => false,
    
    'use_defualt_sorter' => false,
    //...
];
```

... or on the instance itself.
```php
$filterable->disableDefaultFilter();

$filterable->disableDefaultSorter();
```
