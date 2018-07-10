<?php

namespace KoenHoeijmakers\LaravelFilterable\Filters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter;

abstract class AbstractFilter implements Filter
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var string
     */
    protected $column;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $column
     * @param                                       $value
     */
    public function __construct(Builder $builder, string $column, $value)
    {
        $this->builder = $builder;
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function handle();
}