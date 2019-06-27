<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filtering
{
    /**
     * @param  string   $key
     * @param  callable $callable
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function filterFor(string $key, callable $callable): Filtering;

    /**
     * @param  string        $key
     * @param  callable|null $callable
     * @return $this
     */
    public function sortFor(string $key, ?callable $callable = null): Filtering;

    /**
     * @param  string $key
     * @param  bool   $desc
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function defaultSorting(string $key, bool $desc = false): Filtering;

    /**
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return \KoenHoeijmakers\LaravelFilterable\Contracts\Filtering
     */
    public function builder(Builder $builder): Filtering;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filter(): Builder;
}
