<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Discovery\PluginDiscovery;
use Syscage\Engine\Filesystem\Filesystem;
use Syscage\Engine\Support\PathResolver;
use Syscage\Engine\Tests\TestCase;

final class DiscoveryTest extends TestCase
{
    public function test_plugin_discovery_finds_fixture_plugins(): void
    {
        $paths = new PathResolver($this->app['config'], $this->app->basePath());
        $this->app['config']->set('engine.plugins_path', __DIR__ . '/../Fixtures/plugins');

        $discovery = new PluginDiscovery(new Filesystem(), $paths);

        $manifests = $discovery->discover();

        $this->assertArrayHasKey('demo-plugin', $manifests);
        $this->assertSame('Demo Plugin', $manifests['demo-plugin']->displayName());
        $this->assertSame('plugin', $discovery->identifier());
    }

    public function test_plugin_discovery_returns_empty_array_when_directory_is_missing(): void
    {
        $paths = new PathResolver($this->app['config'], $this->app->basePath());
        $this->app['config']->set('engine.plugins_path', '/this/path/does/not/exist');

        $discovery = new PluginDiscovery(new Filesystem(), $paths);

        $this->assertSame([], $discovery->discover());
    }
}
