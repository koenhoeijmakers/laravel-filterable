<?php

use KoenHoeijmakers\LaravelFilterable\Filters\Equal;
use KoenHoeijmakers\LaravelFilterable\Filters\Like;
use KoenHoeijmakers\LaravelFilterable\Sorters\OrderBy;

return [

    /*
    |--------------------------------------------------------------------------
    | Default
    |--------------------------------------------------------------------------
    |
    | These keys assign the default filter and sorter for when no other
    | sorter or filter was assigned.
    |
    */
    'default' => [
        'filter' => Like::class,
        'sorter' => OrderBy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    |
    | These are filters that can be assigned by a string value, for easy
    | access.
    |
    */
    'filters' => [
        'equal' => Equal::class,
        'like'  => Like::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sorters
    |--------------------------------------------------------------------------
    |
    | These are sorters that can be assigned by a string value, for easy
    | access.
    |
    */
    'sorters' => [
        'order_by' => OrderBy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Keys
    |--------------------------------------------------------------------------
    |
    | These are the keys for certain parameters in the request.
    |
    | Dot notation is allowed.
    |
    */
    'keys'    => [
        'filter'    => 'q',
        'sort_by'   => 'sortBy',
        'sort_desc' => 'sortDesc',
    ],
];
