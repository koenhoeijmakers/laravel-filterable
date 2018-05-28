<?php

use KoenHoeijmakers\LaravelFilterable\Filters\Equal;
use KoenHoeijmakers\LaravelFilterable\Filters\Like;
use KoenHoeijmakers\LaravelFilterable\Sorters\OrderBy;

return [
    'default' => [
        'filter' => Like::class,
        'sorter' => OrderBy::class,
    ],
    'filters'  => [
        'equal' => Equal::class,
        'like'  => Like::class,
    ],
    'sorters'  => [
        'order_by' => OrderBy::class,
    ],
];
