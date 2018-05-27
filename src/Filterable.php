<?php

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter;
use KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException;

class Filterable
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $sorters;

    /**
     * Filter constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     */
    public function __construct(Repository $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param string $model
     * @return \KoenHoeijmakers\LaravelFilterable\Filterable
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    public function model(string $model)
    {
        if (!class_exists($model)) {
            throw new FilterException(
                sprintf('Unknown class [%s] passed to [%s]', $model, static::class)
            );
        }

        return static::query($model::query());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \KoenHoeijmakers\LaravelFilterable\Filterable
     */
    public function query(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function registerFilters(array $filters)
    {
        foreach ($filters as $key => $filter) {
            $this->registerFilter($key, $this->parseFilter($filter));
        }

        return $this;
    }

    /**
     * @param $filter
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function parseFilter($filter)
    {
        if (in_array($filter, ['equal', 'like'])) {
            return $this->getDefaultFilter($filter);
        }

        if ($filter instanceof Filter) {
            return $filter;
        }

        return $filter;
    }

    /**
     * @param $filter
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getDefaultFilter($filter)
    {
        return $this->config->get('filterable.defaults.filters.' . $filter);
    }

    /**
     * The default sorter.
     *
     * @return mixed
     */
    protected function getDefaultSorter()
    {
        return $this->config->get('filterable.defaults.sorters.order_by');
    }

    /**
     * @param string                                                      $key
     * @param \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter $filter
     * @return $this
     */
    public function registerFilter(string $key, Filter $filter)
    {
        $this->filters[$key] = $filter;

        return $this;
    }

    /**
     * @param array $sorters
     * @return $this
     */
    public function registerSorters(array $sorters)
    {
        foreach ($sorters as $key => $sorter) {
            $this->registerSorter($key, $sorter);
        }

        return $this;
    }

    /**
     * @param string                                                      $key
     * @param \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter $sorter
     * @return $this
     */
    public function registerSorter(string $key, Sorter $sorter)
    {
        $this->sorters[$key] = $sorter;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasCustomSorter(string $key)
    {
        return array_key_exists($key, $this->sorters);
    }
}
