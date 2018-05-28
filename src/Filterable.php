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
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    protected function parseFilter($filter)
    {
        if ($filter instanceof Filter) {
            return $filter;
        }

        if (in_array($filter, array_keys($this->config->get('filterable.filters')))) {
            return $this->getPlainFilter($filter);
        }

        throw new FilterException(
            sprintf('Class [%s] is not a valid filter.', $filter)
        );
    }

    /**
     * @param $filter
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getPlainFilter($filter)
    {
        return $this->config->get('filterable.filters.' . $filter);
    }

    /**
     * The default filter.
     *
     * @return mixed
     */
    protected function getDefaultFilter()
    {
        return $this->config->get('filterable.default.filter');
    }

    /**
     * The default sorter.
     *
     * @return mixed
     */
    protected function getDefaultSorter()
    {
        return $this->config->get('filterable.default.sorter');
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
    protected function hasSorter(string $key)
    {
        return array_key_exists($key, $this->sorters);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getSorter(string $key)
    {
        return $this->sorters[$key];
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasFilter(string $key)
    {
        return array_key_exists($key, $this->filters);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getFilter(string $key)
    {
        return $this->filters[$key];
    }

    /**
     * Call the registered filters.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filter()
    {
        $this->handleFiltering();

        $this->handleSorting();

        return $this->getBuilder();
    }

    /**
     * Get the builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Handle the filtering.
     *
     * @return void
     */
    protected function handleFiltering()
    {
        foreach ($this->request->input($this->config->get('filterable.keys.filter')) as $key => $value) {
            if (!$this->hasFilter($key)) {
                continue;
            }

            $invokable = $this->getFilter($key);

            /** @var \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter $invokable */
            $invokable = new $invokable();

            $invokable($this->getBuilder(), $key, $value);
        }
    }

    /**
     * Handle the sorting.
     *
     * @return void
     */
    protected function handleSorting()
    {
        $sortBy = $this->request->input($this->config->get('filterable.keys.sortBy'));
        $sortDesc = $this->request->input($this->config->get('filterable.keys.sortDesc'), false);

        if (empty($sortBy)) {
            return;
        }

        $invokable = $this->hasSorter($sortBy) ? $this->getSorter($sortBy) : $this->getDefaultSorter();

        /** @var \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter $invokable */
        $invokable = new $invokable();

        $invokable($this->getBuilder(), $sortBy, $sortDesc ? 'desc' : 'asc');
    }
}
