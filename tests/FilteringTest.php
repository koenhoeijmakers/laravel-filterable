<?php

namespace Tests\Unit\Http;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use KoenHoeijmakers\LaravelFilterable\Filtering;
use KoenHoeijmakers\LaravelFilterable\Tests\TestCase;
use KoenHoeijmakers\LaravelFilterable\Tests\TestModel;

class FilteringTest extends TestCase
{
    /**
     * @var Filtering
     */
    protected $filtering;

    /**
     * @var Request
     */
    protected $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->filtering = $this->app->make(Filtering::class)->builder(TestModel::query());
        $this->request = $this->app->make(Request::class);
    }

    public function testFilterReturnsBuilder()
    {
        $this->assertInstanceOf(Builder::class, $this->filtering->filter());
    }

    public function testRegisteredFilterForWorks()
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

    public function testWorksWithDotNotation()
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

    public function testRegisteredSortForWorksImplicitly()
    {
        $this->request->replace(['sortBy' => 'name', 'desc' => true]);

        $builder = $this->filtering->sortFor('name')->filter();

        $this->assertEquals(
            'select * from "test_models" order by "name" desc',
            $builder->toSql()
        );
    }

    public function testRegisteredSortForWorksExplicitly()
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

    public function testSendingAnArrayDoesntApplyFilters()
    {
        $this->request->replace(['q' => ['a' => 'yoink']]);

        $builder = $this->filtering->filterFor('q', function (Builder $builder, $value) {
            $builder->orderBy('name', $value);
        })->filter();

        $this->assertEquals(
            'select * from "test_models"',
            $builder->toSql()
        );
    }

    public function testSendingAnUnregisteredSorterDoesntSort()
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

    public function testDefaultSorting()
    {
        $builder = $this->filtering->sortFor('name')->defaultSorting('name', true)->filter();

        $this->assertEquals(
            'select * from "test_models" order by "name" desc',
            $builder->toSql()
        );
    }
}
