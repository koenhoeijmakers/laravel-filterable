<?php

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Filter the given query.
     *
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    protected function filter(Builder $query, Request $request)
    {
        if ($request->filled('q')) {
            $this->filterSearch($query, $request->input('q', []));
        }

        $this->filterSort($query, $request);
    }

    /**
     * Handle the filters for the search functionality.
     *
     * @param Builder      $query
     * @param array|string $filter
     * @return void
     */
    protected function filterSearch(Builder $query, $filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $column => $value) {
                $this->filterFor($query, $column, $value);
            }
        } else {
            $this->filterString($query, $filter);
        }
    }

    /**
     * Handle the sorting for the sort functionality.
     *
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    protected function filterSort(Builder $query, Request $request)
    {
        $sortAsc = !$request->filled('sort-desc');

        $sort = $sortAsc ? 'asc' : 'desc';
        $column = $sortAsc ? $request->input('sort-asc') : $request->input('sort-desc');

        if (!empty($column)) {
            $query->orderBy($column, $sort);
        } else {
            $this->filterSortDefault($query);
        }
    }

    /**
     * The filtering for when only a single value is given.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $filter
     * @return void
     */
    abstract protected function filterString(Builder $query, $filter);

    /**
     * The default sorting.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    abstract protected function filterSortDefault(Builder $query);

    /**
     * The filtering for when an array of values is given.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $column
     * @param                                       $value
     * @return void
     */
    protected function filterFor(Builder $query, $column, $value)
    {
        if ($this->methodExists($method = $this->getFilterMethodName($column))) {
            $this->{$method}($query, $value);
        } else {
            $query->where($column, 'LIKE', $value);
        }
    }

    /**
     * Check if the class has the given method.
     *
     * @param $method
     * @return bool
     */
    protected function methodExists($method)
    {
        return method_exists($this, $method);
    }

    /**
     * Get the filter method name.
     *
     * @param $column
     * @return string
     */
    protected function getFilterMethodName($column)
    {
        return 'filterFor' . Str::studly($column);
    }
}
