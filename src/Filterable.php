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
    protected $filters = [];

    /**
     * @var array
     */
    protected $sorters = [];

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
     */
    public function model(string $model)
    {
        return $this->query($model::query());
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
            $this->registerFilter($key, $filter);
        }

        return $this;
    }

    /**
     * @param $filter
     * @return string|\KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    protected function parseFilter($filter)
    {
        if (is_string($filter) && in_array($filter, array_keys($this->config->get('filterable.filters')))) {
            $filter = $this->config->get('filterable.filters.' . $filter);
        }

        if (is_a($filter, Filter::class)) {
            return $filter;
        }

        throw new FilterException(
            'Class [' . $filter . '] is not a valid filter.'
        );
    }

    /**
     * @param string                                                             $key
     * @param \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter|string $filter
     * @return $this
     */
    public function registerFilter(string $key, $filter)
    {
        $this->filters[$key] = $this->parseFilter($filter);

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
     * @param $sorter
     * @return string|\KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    protected function parseSorter($sorter)
    {
        if (is_string($sorter) && in_array($sorter, array_keys($this->config->get('filterable.sorters')))) {
            $sorter = $this->config->get('filterable.sorters.' . $sorter);
        }

        if (is_a($sorter, Sorter::class)) {
            return $sorter;
        }

        throw new FilterException(
            'Class [' . $sorter . '] is not a valid filter.'
        );
    }

    /**
     * @param string                                                             $key
     * @param \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter|string $sorter
     * @return $this
     */
    public function registerSorter(string $key, $sorter)
    {
        $this->sorters[$key] = $this->parseSorter($sorter);

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
            $invokable = $this->getInvokableSorter($key);

            $invokable($this->getBuilder(), $key, $value);
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getInvokableFilter(string $key)
    {
        $invokable = $this->hasFilter($key) ? $this->getFilter($key) : $this->config->get('filterable.default.filter');

        if (!$invokable instanceof Filter) {
            $invokable = new $invokable();
        }

        return $invokable;
    }

    /**
     * Handle the sorting.
     *
     * @return void
     */
    protected function handleSorting()
    {
        $sortBy = $this->request->input($this->config->get('filterable.keys.sortBy'));
        $sortDesc = $this->request->input($this->config->get('filterable.keys.sortDesc'));

        if (empty($sortBy)) {
            return;
        }

        $invokable = $this->getInvokableSorter($sortBy);

        $invokable($this->getBuilder(), $sortBy, $sortDesc ? 'desc' : 'asc');
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getInvokableSorter(string $key)
    {
        $invokable = $this->hasSorter($key) ? $this->getSorter($key) : $this->config->get('filterable.default.sorter');

        if (!$invokable instanceof Sorter) {
            $invokable = new $invokable();
        }

        return $invokable;
    }
}
