<?php

namespace KoenHoeijmakers\LaravelFilterable\Sorters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter;

class OrderBy extends AbstractSorter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $builder, string $type)
    {
        return $builder->orderBy($this->getColumn(), $type);
    }
}
