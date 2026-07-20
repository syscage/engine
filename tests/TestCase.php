<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Syscage\Engine\Providers\EngineServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EngineServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('engine.discovery.eager', false);
        $app['config']->set('engine.plugins_path', __DIR__ . '/Fixtures/plugins');
    }
}
