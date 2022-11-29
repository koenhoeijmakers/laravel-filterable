<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable;

use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering as FilteringContract;
use function is_callable;
use function array_key_exists;

class Filtering implements FilteringContract
{
    protected Request $request;

    protected Repository $config;

    protected EloquentBuilderContract|QueryBuilderContract $builder;

    protected array $filters = [];

    protected array $sorters = [];

    protected ?string $defaultSortBy = null;

    protected bool $defaultSortDesc = false;

    public function __construct(Request $request, Repository $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    public function filterFor(string $key, callable $callable): FilteringContract
    {
        $this->filters[$key] = $callable;

        return $this;
    }

    public function sortFor(string $key, ?callable $callable = null): FilteringContract
    {
        $this->sorters[$key] = $callable;

        return $this;
    }

    public function defaultSorting(string $key, bool $desc = false): FilteringContract
    {
        $this->defaultSortBy = $key;
        $this->defaultSortDesc = $desc;

        return $this;
    }

    public function builder(EloquentBuilderContract|QueryBuilderContract $builder): FilteringContract
    {
        $this->builder = $builder;

        return $this;
    }

    public function filter(): EloquentBuilderContract|QueryBuilderContract
    {
        $this->doFiltering();
        $this->doSorting();

        return $this->builder;
    }

    protected function doFiltering(): void
    {
        foreach ($this->filters as $key => $callable) {
            if ($this->request->filled($key)) {
                $callable($this->builder, $this->request->input($key));
            }
        }
    }

    protected function doSorting(): void
    {
        $sortBy = $this->getSortBy();
        $direction = $this->getDesc() ? 'desc' : 'asc';

        if (null === $sortBy) {
            return;
        }

        if (! array_key_exists($sortBy, $this->sorters)) {
            return;
        }

        $callable = $this->sorters[$sortBy];

        if (is_callable($callable)) {
            $callable($this->builder, $direction);

            return;
        }

        $this->builder->orderBy($sortBy, $direction);
    }

    protected function getDesc(): bool
    {
        return (bool) $this->request->input(
            $this->config->get('filterable.keys.sort_desc'),
            $this->defaultSortDesc
        );
    }

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
