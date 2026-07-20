<?php

declare(strict_types=1);

namespace Syscage\Engine\Bootstrap;

/**
 * Registers everything the engine allows applications to publish:
 * its configuration file and its default stub templates.
 */
trait RegistersPublishing
{
    protected function registerPublishing(string $basePath): void
    {
        $this->publishes([
            $basePath . '/config/engine.php' => config_path('engine.php'),
        ], 'engine-config');

        $this->publishes([
            $basePath . '/stubs' => base_path('stubs/syscage'),
        ], 'engine-stubs');
    }
}
