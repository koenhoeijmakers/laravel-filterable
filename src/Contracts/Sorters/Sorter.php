<?php

namespace KoenHoeijmakers\LaravelFilterable\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Sorter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $column
     * @param string                                $type
     */
    public function __construct(Builder $builder, string $column, string $type);

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle();
}
