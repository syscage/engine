<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Core\PackageManifest;
use Syscage\Engine\Exceptions\InvalidManifestException;
use Syscage\Engine\Tests\TestCase;

final class PackageManifestTest extends TestCase
{
    public function test_it_can_be_built_from_an_array(): void
    {
        $manifest = PackageManifest::fromArray([
            'name' => 'whatsapp',
            'display_name' => 'WhatsApp',
            'description' => 'WhatsApp integration',
            'version' => '1.2.3',
            'enabled' => true,
            'provider' => 'Plugin\\WhatsApp\\Providers\\PluginServiceProvider',
        ], '/plugins/whatsapp');

        $this->assertSame('whatsapp', $manifest->name());
        $this->assertSame('WhatsApp', $manifest->displayName());
        $this->assertSame('WhatsApp integration', $manifest->description());
        $this->assertSame('1.2.3', $manifest->version());
        $this->assertTrue($manifest->isEnabled());
        $this->assertSame('Plugin\\WhatsApp\\Providers\\PluginServiceProvider', $manifest->provider());
        $this->assertSame('/plugins/whatsapp', $manifest->basePath());
    }

    public function test_it_defaults_display_name_to_name_when_absent(): void
    {
        $manifest = PackageManifest::fromArray(['name' => 'billing'], '/plugins/billing');

        $this->assertSame('billing', $manifest->displayName());
        $this->assertSame('0.0.0', $manifest->version());
        $this->assertTrue($manifest->isEnabled());
        $this->assertNull($manifest->provider());
    }

    public function test_it_rejects_a_manifest_without_a_name(): void
    {
        $this->expectException(InvalidManifestException::class);

        PackageManifest::fromArray(['description' => 'no name here'], '/plugins/broken');
    }

    public function test_it_can_be_loaded_from_a_json_file(): void
    {
        $path = __DIR__ . '/../Fixtures/plugins/demo-plugin/plugin.json';

        $manifest = PackageManifest::fromJsonFile($path, dirname($path));

        $this->assertSame('demo-plugin', $manifest->name());
        $this->assertSame('Demo Plugin', $manifest->displayName());
        $this->assertSame('1.0.0', $manifest->version());
    }

    public function test_it_throws_when_the_json_file_is_missing(): void
    {
        $this->expectException(InvalidManifestException::class);

        PackageManifest::fromJsonFile('/nowhere/plugin.json', '/nowhere');
    }
}
