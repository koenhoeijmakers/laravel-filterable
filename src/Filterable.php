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
     * @var bool
     */
    protected $useDefaultFilter;

    /**
     * @var bool
     */
    protected $useDefaultSorter;

    /**
     * @var string|null
     */
    protected $defaultSortBy;

    /**
     * @var bool|null
     */
    protected $defaultSortDesc;

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
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \KoenHoeijmakers\LaravelFilterable\Filterable
     */
    public function query(Builder $builder)
    {
        return $this->builder($builder);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \KoenHoeijmakers\LaravelFilterable\Filterable
     */
    public function builder(Builder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Disable the default filter.
     *
     * @return $this
     */
    public function disableDefaultFilter()
    {
        $this->useDefaultFilter = false;

        return $this;
    }

    /**
     * Enable the default filter.
     *
     * @return $this
     */
    public function enableDefaultFilter()
    {
        $this->useDefaultFilter = false;

        return $this;
    }

    /**
     * Disable the default sorter.
     *
     * @return $this
     */
    public function disableDefaultSorter()
    {
        $this->useDefaultSorter = false;

        return $this;
    }

    /**
     * Enable the default sorter.
     *
     * @return $this
     */
    public function enableDefaultSorter()
    {
        $this->useDefaultSorter = true;

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
     * @param $filter
     * @return string|\KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    protected function parseFilter($filter)
    {
        if (is_string($filter) && in_array($filter, array_keys($this->config->get('filterable.filters')))) {
            $filter = $this->config->get('filterable.filters.' . $filter);
        }

        if (is_subclass_of($filter, Filter::class)) {
            return $filter;
        }

        throw new FilterException(
            'Class [' . $filter . '] is not a valid filter.'
        );
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
     * @param $sorter
     * @return string|\KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter
     * @throws \KoenHoeijmakers\LaravelFilterable\Exceptions\FilterException
     */
    protected function parseSorter($sorter)
    {
        if (is_string($sorter) && in_array($sorter, array_keys($this->config->get('filterable.sorters')))) {
            $sorter = $this->config->get('filterable.sorters.' . $sorter);
        }

        if (is_subclass_of($sorter, Sorter::class)) {
            return $sorter;
        }

        throw new FilterException(
            'Class [' . $sorter . '] is not a valid filter.'
        );
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
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter|string
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
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter|string
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
        $parameters = $this->request->input(
            $this->config->get('filterable.keys.filter')
        );

        if (! is_array($parameters)) {
            return;
        }

        foreach ($parameters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $instance = $this->getFilterInstance($key);

            if ($instance instanceof Filter) {
                $instance->handle($this->getBuilder(), $value);
            }
        }
    }

    /**
     * @param string $key
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Filter|null
     */
    protected function getFilterInstance(string $key)
    {
        if ($this->hasFilter($key)) {
            $instance = $this->getFilter($key);
        } elseif ($this->shouldUseDefaultFilter()) {
            $instance = $this->config->get('filterable.default.filter');
        } else {
            return null;
        }

        return ! $instance instanceof Filter ? new $instance($key) : $instance;
    }

    /**
     * Whether the instance should use the default filter.
     *
     * @return bool
     */
    protected function shouldUseDefaultFilter()
    {
        return isset($this->useDefaultFilter)
            ? $this->useDefaultFilter
            : (bool) $this->config->get('filterable.use_default_filter');
    }

    /**
     * Handle the sorting.
     *
     * @return void
     */
    protected function handleSorting()
    {
        $sortBy = $this->request->input(
            $this->config->get('filterable.keys.sort_by')
        );

        $sortDesc = $this->request->input(
            $this->config->get('filterable.keys.sort_desc')
        );

        if (
            $sortDesc === null &&
            $sortBy === null &&
            $this->defaultSortDesc !== null &&
            $this->defaultSortBy !== null
        ) {
            $sortDesc = $this->defaultSortDesc;
            $sortBy = $this->defaultSortBy;
        }

        if (empty($sortBy)) {
            return;
        }

        $instance = $this->getSorterInstance($sortBy);

        if ($instance instanceof Sorter) {
            $instance->handle($this->getBuilder(), $sortDesc ? 'desc' : 'asc');
        }
    }

    /**
     * @param string $sortBy
     * @param bool   $sortDesc
     * @return $this
     */
    public function setDefaultSorting(string $sortBy, bool $sortDesc = false)
    {
        $this->defaultSortBy = $sortBy;
        $this->defaultSortDesc = $sortDesc;

        return $this;
    }

    /**
     * @param string $key
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filters\Sorter|null
     */
    protected function getSorterInstance(string $key)
    {
        if ($this->hasSorter($key)) {
            $instance = $this->getSorter($key);
        } elseif ($this->shouldUseDefaultSorter()) {
            $instance = $this->config->get('filterable.default.sorter');
        } else {
            return null;
        }

        return ! $instance instanceof Sorter ? new $instance($key) : $instance;
    }

    /**
     * Whether the instance should use the default sorter.
     *
     * @return bool
     */
    protected function shouldUseDefaultSorter()
    {
        return isset($this->useDefaultSorter)
            ? $this->useDefaultSorter
            : (bool) $this->config->get('filterable.use_default_sorter');
    }
}
