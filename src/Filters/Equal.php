<?php

namespace KoenHoeijmakers\LaravelFilterable\Filters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filter;

class Equal implements Filter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param                                       $column
     * @param                                       $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function __invoke(Builder $builder, $column, $value)
    {
        return $builder->where($column, '=', $value);
    }
}