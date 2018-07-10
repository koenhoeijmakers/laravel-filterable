<?php

namespace KoenHoeijmakers\LaravelFilterable\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Sorter
{
    /**
     * @param string $column
     */
    public function __construct(string $column);

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Builder $builder, string $type);
}
