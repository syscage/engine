<?php

declare(strict_types=1);

namespace Syscage\Engine\Tests\Unit;

use Syscage\Engine\Core\PackageManifest;
use Syscage\Engine\Registry\PackageRegistry;
use Syscage\Engine\Tests\TestCase;

final class RegistryTest extends TestCase
{
    public function test_it_registers_and_retrieves_manifests(): void
    {
        $registry = new PackageRegistry();
        $manifest = PackageManifest::fromArray(['name' => 'payment'], '/vendor/syscage/payment');

        $registry->register($manifest);

        $this->assertTrue($registry->has('payment'));
        $this->assertSame($manifest, $registry->get('payment'));
        $this->assertSame(1, $registry->count());
    }

    public function test_get_returns_null_for_unknown_entries(): void
    {
        $registry = new PackageRegistry();

        $this->assertNull($registry->get('unknown'));
        $this->assertFalse($registry->has('unknown'));
    }

    public function test_forget_removes_an_entry(): void
    {
        $registry = new PackageRegistry();
        $registry->register(PackageManifest::fromArray(['name' => 'payment'], '/x'));

        $registry->forget('payment');

        $this->assertFalse($registry->has('payment'));
        $this->assertSame(0, $registry->count());
    }

    public function test_enabled_filters_out_disabled_manifests(): void
    {
        $registry = new PackageRegistry();
        $registry->register(PackageManifest::fromArray(['name' => 'active', 'enabled' => true], '/x'));
        $registry->register(PackageManifest::fromArray(['name' => 'inactive', 'enabled' => false], '/y'));

        $enabled = $registry->enabled();

        $this->assertCount(1, $enabled);
        $this->assertArrayHasKey('active', $enabled);
        $this->assertArrayNotHasKey('inactive', $enabled);
        $this->assertCount(2, $registry->all());
    }

    public function test_registering_the_same_name_twice_overwrites_it(): void
    {
        $registry = new PackageRegistry();
        $registry->register(PackageManifest::fromArray(['name' => 'payment', 'version' => '1.0.0'], '/x'));
        $registry->register(PackageManifest::fromArray(['name' => 'payment', 'version' => '2.0.0'], '/x'));

        $this->assertSame(1, $registry->count());
        $this->assertSame('2.0.0', $registry->get('payment')?->version());
    }
}
