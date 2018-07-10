<?php

namespace KoenHoeijmakers\LaravelFilterable\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * @param string $column
     */
    public function __construct(string $column);

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed                                 $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $builder, $value);
}
