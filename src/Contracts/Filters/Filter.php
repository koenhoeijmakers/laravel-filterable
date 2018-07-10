<?php

namespace KoenHoeijmakers\LaravelFilterable\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $column
     * @param                                       $value
     */
    public function __construct(Builder $builder, string $column, $value);

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle();
}
