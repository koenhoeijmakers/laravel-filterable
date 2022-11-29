<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable\Tests;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use KoenHoeijmakers\LaravelFilterable\Filtering;
use Illuminate\Database\Eloquent\Relations\HasMany;
use KoenHoeijmakers\LaravelFilterable\Contracts\Filtering as FilteringContract;

class FilteringTest extends TestCase
{
    protected FilteringContract $filtering;

    protected Request $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->filtering = $this->app->make(FilteringContract::class)->builder(TestModel::query());
        $this->request = $this->app->make(Request::class);
    }

    public function testResolving(): void
    {
        $this->assertInstanceOf(FilteringContract::class, $this->app->make(FilteringContract::class));
        $this->assertInstanceOf(FilteringContract::class, $this->app->make(Filtering::class));
    }

    public function testFilterReturnsBuilder(): void
    {
        $this->assertInstanceOf(Builder::class, $this->filtering->filter());
    }

    public function testCanPassEloquentBuilder(): void
    {
        $this->assertInstanceOf(HasMany::class,
            $this->filtering->builder((new TestModel())->relation_models())->filter()
        );
    }

    public function testCanPassQueryBuilder(): void
    {
        $this->assertInstanceOf(\Illuminate\Database\Query\Builder::class,
            $this->filtering->builder($this->app->make(\Illuminate\Database\Query\Builder::class))->filter()
        );
    }

    public function testRegisteredFilterForWorks(): void
    {
        $key = 'this-name-does-not-even-make-sense-but-its-explicit-so-what-the-hell';

        $this->request->replace([$key => 'merchant']);

        $builder = $this->filtering->filterFor($key, function (Builder $builder, $value) {
            $builder->where('name', $value);
        })->filter();

        $this->assertEquals(
            'select * from "test_models" where "name" = ?',
            $builder->toSql()
        );
    }

    public function testWorksWithDotNotation(): void
    {
        $key = 'filter.name';

        $this->request->replace(['filter' => ['name' => 'merchant']]);

        $builder = $this->filtering->filterFor($key, function (Builder $builder, $value) {
            $builder->where('name', $value);
        })->filter();

        $this->assertEquals(
            'select * from "test_models" where "name" = ?',
            $builder->toSql()
        );
    }

    public function testRegisteredSortForWorksImplicitly(): void
    {
        $this->request->replace(['sortBy' => 'name', 'desc' => true]);

        $builder = $this->filtering->sortFor('name')->filter();

        $this->assertEquals(
            'select * from "test_models" order by "name" desc',
            $builder->toSql()
        );
    }

    public function testRegisteredSortForWorksExplicitly(): void
    {
        $this->request->replace(['sortBy' => 'name', 'desc' => false]);

        $builder = $this->filtering->sortFor('name', function (Builder $builder, $value) {
            $builder->orderBy('name', $value);
        })->filter();

        $this->assertEquals(
            'select * from "test_models" order by "name" asc',
            $builder->toSql()
        );
    }

    public function testSendingAnArrayDoesntApplyFilters(): void
    {
        $this->request->replace(['q' => ['yoink']]);

        $builder = $this->filtering->filterFor('q', function (Builder $builder, array $value) {
            foreach ($value as $item) {
                $builder->where('name', $item);
            }
        })->filter();

        $this->assertEquals(
            'select * from "test_models" where "name" = ?',
            $builder->toSql()
        );
    }

    public function testSendingAnUnregisteredSorterDoesntSort(): void
    {
        $this->request->replace(['sortBy' => 'non_existent', 'desc' => false]);

        $builder = $this->filtering->sortFor('name', function (Builder $builder, $value) {
            $builder->orderBy('name', $value);
        })->filter();

        $this->assertEquals(
            'select * from "test_models"',
            $builder->toSql()
        );
    }

    public function testDefaultSorting(): void
    {
        $builder = $this->filtering->sortFor('name')->defaultSorting('name', true)->filter();

        $this->assertEquals(
            'select * from "test_models" order by "name" desc',
            $builder->toSql()
        );
    }
}
