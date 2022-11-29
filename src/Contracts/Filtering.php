<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable\Contracts;

use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;

interface Filtering
{
    public function filterFor(string $key, callable $callable): Filtering;

    public function sortFor(string $key, ?callable $callable = null): Filtering;

    public function defaultSorting(string $key, bool $desc = false): Filtering;

    public function builder(EloquentBuilderContract|QueryBuilderContract $builder): Filtering;

    public function filter(): EloquentBuilderContract|QueryBuilderContract;
}
