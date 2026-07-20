<?php

declare(strict_types=1);

namespace Syscage\Engine\Bootstrap;

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\StubRendererContract;
use Syscage\Engine\Core\EngineManager;
use Syscage\Engine\Filesystem\Filesystem;
use Syscage\Engine\Generator\ClassGenerator;
use Syscage\Engine\Generator\StubEngine;
use Syscage\Engine\Registry\PackageRegistry;
use Syscage\Engine\Registry\PluginRegistry;
use Syscage\Engine\Support\PathResolver;

/**
 * Registers every container binding the engine exposes. Extracted
 * from the service provider itself so `EngineServiceProvider` stays
 * a thin orchestrator (Single Responsibility).
 */
trait RegistersBindings
{
    protected function registerBindings(Application $app): void
    {
        $app->singleton(FilesystemContract::class, Filesystem::class);

        $app->singleton(PathResolver::class, static fn (Application $app): PathResolver => new PathResolver(
            $app->make(ConfigContract::class),
            $app->basePath(),
        ));

        $app->singleton(PackageRegistry::class, static fn (Application $app): PackageRegistry => new PackageRegistry(
            $app->bound('events') ? $app->make('events') : null,
        ));

        $app->singleton(PluginRegistry::class, static fn (Application $app): PluginRegistry => new PluginRegistry(
            $app->bound('events') ? $app->make('events') : null,
        ));

        $app->singleton(StubEngine::class, static fn (Application $app): StubEngine => new StubEngine(
            $app->make(FilesystemContract::class),
            $app->make(PathResolver::class),
        ));

        $app->singleton(StubRendererContract::class, StubEngine::class);

        $app->singleton(ClassGenerator::class, static fn (Application $app): ClassGenerator => new ClassGenerator(
            $app->make(FilesystemContract::class),
            $app->make(StubRendererContract::class),
        ));

        $app->singleton(EngineManager::class, static fn (Application $app): EngineManager => new EngineManager(
            $app->make(PackageRegistry::class),
            $app->make(PluginRegistry::class),
            $app->make(PathResolver::class),
            $app->make(StubEngine::class),
            $app->make(ClassGenerator::class),
            $app->make(FilesystemContract::class),
            $app->bound('events') ? $app->make('events') : null,
        ));
    }
}
