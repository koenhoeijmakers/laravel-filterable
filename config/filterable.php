<?php

return [
    'defaults' => [
        'filters' => [
            'equal' => \KoenHoeijmakers\LaravelFilterable\Filters\Equal::class,
            'like'  => \KoenHoeijmakers\LaravelFilterable\Filters\Like::class,
        ],
        'sorters' => [
            'order_by' => \KoenHoeijmakers\LaravelFilterable\Sorters\OrderBy::class,
        ],
    ],
];
