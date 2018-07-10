<?php

namespace KoenHoeijmakers\LaravelFilterable\Sorters;

use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter;

abstract class AbstractSorter implements Sorter
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
     * @param string                                $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function handle(Builder $builder, string $type);

    /**
     * Get the column.
     *
     * @return string
     */
    protected function getColumn()
    {
        return $this->column;
    }
}