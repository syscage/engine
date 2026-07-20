<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Support\PathResolver;
use Syscage\Engine\Tests\TestCase;

final class PathResolverTest extends TestCase
{
    public function test_it_resolves_relative_plugins_path_against_base_path(): void
    {
        $this->app['config']->set('engine.plugins_path', 'plugins');

        $paths = new PathResolver($this->app['config'], '/var/www/app');

        $this->assertSame('/var/www/app/plugins', $paths->plugins());
        $this->assertSame('/var/www/app/plugins/whatsapp', $paths->plugin('whatsapp'));
    }

    public function test_it_leaves_absolute_plugins_path_untouched(): void
    {
        $this->app['config']->set('engine.plugins_path', '/mnt/data/plugins');

        $paths = new PathResolver($this->app['config'], '/var/www/app');

        $this->assertSame('/mnt/data/plugins', $paths->plugins());
    }

    public function test_it_resolves_the_engine_packages_own_default_stubs_directory(): void
    {
        $paths = new PathResolver($this->app['config'], '/var/www/app');

        $this->assertStringEndsWith('stubs/class.stub', $paths->defaultStubs('class.stub'));
    }

    public function test_base_appends_a_path_segment(): void
    {
        $paths = new PathResolver($this->app['config'], '/var/www/app');

        $this->assertSame('/var/www/app/vendor/composer/installed.json', $paths->base('vendor/composer/installed.json'));
        $this->assertSame('/var/www/app', $paths->base());
    }
}
