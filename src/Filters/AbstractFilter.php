<?php

namespace KoenHoeijmakers\LaravelFilterable\Filters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter;

abstract class AbstractFilter implements Filter
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
    abstract public function handle(Builder $builder, $value);
}