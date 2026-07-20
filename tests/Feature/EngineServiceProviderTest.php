<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Feature;

use Syscage\Engine\Core\EngineManager;
use Syscage\Engine\Facades\Engine;
use Syscage\Engine\Registry\PackageRegistry;
use Syscage\Engine\Registry\PluginRegistry;
use Syscage\Engine\Tests\TestCase;

final class EngineServiceProviderTest extends TestCase
{
    public function test_the_engine_manager_is_bound_as_a_singleton(): void
    {
        $this->assertTrue($this->app->bound(EngineManager::class));
        $this->assertSame(
            $this->app->make(EngineManager::class),
            $this->app->make(EngineManager::class),
        );
    }

    public function test_the_facade_resolves_to_the_engine_manager(): void
    {
        $this->assertInstanceOf(PackageRegistry::class, Engine::packages());
        $this->assertInstanceOf(PluginRegistry::class, Engine::plugins());
    }

    public function test_discover_all_populates_the_plugin_registry_from_fixtures(): void
    {
        Engine::discoverAll();

        $this->assertTrue(Engine::plugins()->has('demo-plugin'));
    }

    public function test_config_is_merged_with_sensible_defaults(): void
    {
        $this->assertSame('bootstrap/cache', config('engine.cache.path'));
    }
}
