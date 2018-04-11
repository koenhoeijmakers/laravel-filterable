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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter(Builder $query, Request $request): Builder
    {
        if ($request->filled('q')) {
            $this->filterSearch($query, $request->input('q', []));
        }

        $this->filterSort($query, $request);

        return $query;
    }

    /**
     * Handle the filters for the search functionality.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string                          $filter
     * @return void
     */
    protected function filterSearch(Builder $query, $filter)
    {
        if (is_array($filter)) {
            foreach ($filter as $column => $value) {
                $this->filterFor($query, $column, $value);
            }
        } else {
            if (method_exists($this, 'filterString')) {
                $this->filterString($query, $filter);
            }
        }
    }

    /**
     * Handle the sorting for the sort functionality.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request              $request
     * @return void
     */
    protected function filterSort(Builder $query, Request $request)
    {
        if (!empty($sortBy = $request->input('sortBy'))) {
            $query->orderBy($sortBy, $request->input('desc', false) ? 'desc' : 'asc');
        } else {
            if (method_exists($this, 'filterSortDefault')) {
                $this->filterSortDefault($query);
            }
        }
    }

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
        if (method_exists($this, $method = $this->getFilterMethodName($column))) {
            $this->{$method}($query, $value);
        } else {
            $query->where($column, 'LIKE', $value);
        }
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
