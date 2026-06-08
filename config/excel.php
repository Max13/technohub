<?php

return [
    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Default cell caching driver
        |--------------------------------------------------------------------------
        |
        | By default PhpSpreadsheet keeps all cell values in memory, however when
        | dealing with large files, this might result into memory issues. If you
        | want to mitigate that, you can configure a cell caching driver here.
        | When using the illuminate driver, it will store each value in the
        | cache store. This can slow down the process, because it needs to
        | store each value. You can use the "batch" store if you want to
        | only persist to the store when the memory limit is reached.
        |
        | Drivers: memory|illuminate|batch
        |
        */
        'driver' => 'illuminate',

        /*
        |--------------------------------------------------------------------------
        | Illuminate cache
        |--------------------------------------------------------------------------
        |
        | When using the "illuminate" caching driver, it will automatically use
        | your default cache store. However if you prefer to have the cell
        | cache on a separate store, you can configure the store name here.
        | You can use any store defined in your cache config. When leaving
        | at "null" it will use the default store.
        |
        */
        'illuminate' => [
            'store' => null,
        ],
    ],
];
