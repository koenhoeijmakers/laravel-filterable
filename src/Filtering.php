<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering as FilteringContract;
use function array_key_exists;
use function is_callable;

class Filtering implements FilteringContract
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $sorters = [];

    /**
     * @var string|null
     */
    protected $defaultSortBy = null;

    /**
     * @var bool
     */
    protected $defaultSortDesc = false;

    /**
     * Filtering constructor.
     *
     * @param  \Illuminate\Http\Request                $request
     * @param  \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Request $request, Repository $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param  string   $key
     * @param  callable $callable
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function filterFor(string $key, callable $callable): FilteringContract
    {
        $this->filters[$key] = $callable;

        return $this;
    }

    /**
     * @param  string        $key
     * @param  callable|null $callable
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function sortFor(string $key, ?callable $callable = null): FilteringContract
    {
        $this->sorters[$key] = $callable;

        return $this;
    }

    /**
     * @param  string $key
     * @param  bool   $desc
     * @return $this
     */
    public function defaultSorting(string $key, bool $desc = false): FilteringContract
    {
        $this->defaultSortBy = $key;
        $this->defaultSortDesc = $desc;

        return $this;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function builder(Builder $builder): FilteringContract
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filter(): Builder
    {
        $this->doFiltering();
        $this->doSorting();

        return $this->builder;
    }

    /**
     * Handles the filtering.
     *
     * @return void
     */
    protected function doFiltering(): void
    {
        foreach ($this->filters as $key => $callable) {
            if ($this->request->filled($key)) {
                $callable($this->builder, $this->request->input($key));
            }
        }
    }

    /**
     * Handles the sorting.
     *
     * @return void
     */
    protected function doSorting(): void
    {
        $sortBy = $this->getSortBy();
        $type = $this->getDesc() ? 'desc' : 'asc';

        if (null === $sortBy) {
            return;
        }

        if (! array_key_exists($sortBy, $this->sorters)) {
            return;
        }

        $callable = $this->sorters[$sortBy];

        if (is_callable($callable)) {
            $callable($this->builder, $type);

            return;
        }

        $this->builder->orderBy($sortBy, $type);
    }

    /**
     * @return bool
     */
    protected function getDesc(): bool
    {
        return (bool) $this->request->input(
            $this->config->get('filterable.keys.sort_desc'),
            $this->defaultSortDesc
        );
    }

    /**
     * @return string|null
     */
    protected function getSortBy(): ?string
    {
        $sortBy = $this->request->input(
            $this->config->get('filterable.keys.sort_by')
        );

        if (null === $sortBy && null !== $this->defaultSortBy) {
            $sortBy = $this->defaultSortBy;
        }

        return $sortBy;
    }
}
