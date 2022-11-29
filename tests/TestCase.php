<?php

declare(strict_types=1);

namespace KoenHoeijmakers\LaravelFilterable\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use KoenHoeijmakers\LaravelFilterable\FilterableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [FilterableServiceProvider::class];
    }

    protected function setUpDatabase()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }
}

class TestModel extends Model
{
    protected $fillable = ['name'];

    public function relation_models(): HasMany
    {
        return $this->hasMany(TestRelationModel::class);
    }
}

class TestRelationModel extends Model
{
    protected $fillable = ['name'];
}
