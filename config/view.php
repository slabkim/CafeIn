<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    // On immutable filesystems (e.g. Vercel), writing to storage/framework/views fails.
    // Prefer storage if writable, otherwise fall back to a temp dir. Can be overridden via VIEW_COMPILED_PATH.
    'compiled' => env('VIEW_COMPILED_PATH', (function () {
        $default = storage_path('framework/views');

        if (! is_dir($default) && is_writable(dirname($default))) {
            @mkdir($default, 0755, true);
        }

        if (is_dir($default) && is_writable($default)) {
            return $default;
        }

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'blade';
    })()),

];
