<?php

namespace KoenHoeijmakers\LaravelFilterable\Filters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter;

class Equal extends AbstractFilter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param  mixed                                $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $builder, $value)
    {
        return $builder->where($this->getColumn(), '=', $value);
    }
}
