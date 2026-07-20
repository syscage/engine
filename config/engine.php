<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Plugins Path
    |--------------------------------------------------------------------------
    |
    | The directory (relative to the application base path, or absolute)
    | in which SysCage plugins are stored. Each subdirectory is expected
    | to contain its own `plugin.json` manifest.
    |
    */
    'plugins_path' => env('SYSCAGE_PLUGINS_PATH', 'plugins'),

    /*
    |--------------------------------------------------------------------------
    | Discovery
    |--------------------------------------------------------------------------
    |
    | Controls when package/plugin discovery runs. When "eager" is true,
    | discovery runs automatically during the engine's boot() call on
    | every request; disable it in production and rely on the cached
    | registries populated by `php artisan engine:discover` instead.
    |
    */
    'discovery' => [
        'eager' => env('SYSCAGE_DISCOVERY_EAGER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Publishing
    |--------------------------------------------------------------------------
    |
    | Where user-published stub overrides are looked for before falling
    | back to a package's own bundled default stubs.
    |
    */
    'stubs' => [
        'publish_path' => env('SYSCAGE_STUBS_PATH', 'stubs/syscage'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Where the engine may persist a compiled discovery cache, mirroring
    | Laravel's own config/route/event cache conventions.
    |
    */
    'cache' => [
        'path' => env('SYSCAGE_CACHE_PATH', 'bootstrap/cache'),
    ],

];
