<?php

declare(strict_types=1);

namespace Syscage\Engine\Core;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Discovery\DriverDiscovery;
use Syscage\Engine\Discovery\PackageDiscovery;
use Syscage\Engine\Discovery\PluginDiscovery;
use Syscage\Engine\Events\EngineBooted;
use Syscage\Engine\Exceptions\PackageNotFoundException;
use Syscage\Engine\Generator\ClassGenerator;
use Syscage\Engine\Generator\StubEngine;
use Syscage\Engine\Registry\PackageRegistry;
use Syscage\Engine\Registry\PluginRegistry;
use Syscage\Engine\Support\PathResolver;

/**
 * The single object the `Engine` facade resolves to. It does not
 * contain any plugin-specific business logic — it only wires
 * together the generic services every SysCage package depends on:
 * registries, discovery, path resolution, and code generation.
 */
final class EngineManager
{
    public function __construct(
        private readonly PackageRegistry $packages,
        private readonly PluginRegistry $plugins,
        private readonly PathResolver $paths,
        private readonly StubEngine $stubs,
        private readonly ClassGenerator $generator,
        private readonly FilesystemContract $files,
        private readonly ?Dispatcher $events = null,
    ) {
    }

    public function packages(): PackageRegistry
    {
        return $this->packages;
    }

    public function plugins(): PluginRegistry
    {
        return $this->plugins;
    }

    public function paths(): PathResolver
    {
        return $this->paths;
    }

    public function stubs(): StubEngine
    {
        return $this->stubs;
    }

    public function generator(): ClassGenerator
    {
        return $this->generator;
    }

    /**
     * Find a registered package or plugin by name, checking plugins
     * first since they are the more common lookup during runtime.
     */
    public function find(string $name): PackageManifestContract
    {
        return $this->plugins->get($name)
            ?? $this->packages->get($name)
            ?? throw PackageNotFoundException::withName($name);
    }

    /**
     * Run the plugin and package discoveries and populate their
     * respective registries. Idempotent — safe to call more than once.
     */
    public function discoverAll(): void
    {
        $pluginDiscovery = new PluginDiscovery($this->files, $this->paths, $this->events);

        foreach ($pluginDiscovery->discover() as $manifest) {
            $this->plugins->register($manifest);
        }

        $packageDiscovery = new PackageDiscovery($this->files, $this->paths, events: $this->events);

        foreach ($packageDiscovery->discover() as $manifest) {
            $this->packages->register($manifest);
        }

        $this->events?->dispatch(new EngineBooted($this->packages->count(), $this->plugins->count()));
    }

    /**
     * Discover classes implementing a given interface under a directory,
     * used by dependent packages to auto-discover drivers.
     *
     * @return array<string, PackageManifestContract>
     */
    public function discoverDrivers(string $directory, string $namespace, string $interface): array
    {
        return (new DriverDiscovery($this->files, $directory, $namespace, $interface, $this->events))
            ->discover();
    }
}
