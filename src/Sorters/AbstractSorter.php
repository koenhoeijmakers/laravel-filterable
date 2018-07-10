<?php

namespace KoenHoeijmakers\LaravelFilterable\Sorters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter;

abstract class AbstractSorter implements Sorter
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
     * @var string
     */
    protected $type;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $column
     * @param string                                $type
     */
    public function __construct(Builder $builder, string $column, string $type)
    {
        $this->builder = $builder;
        $this->column = $column;
        $this->type = $type;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function handle();
}