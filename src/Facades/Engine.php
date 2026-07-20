<?php

declare(strict_types=1);

namespace Syscage\Engine\Facades;

use Illuminate\Support\Facades\Facade;
use Syscage\Engine\Core\EngineManager;

/**
 * @method static \Syscage\Engine\Registry\PackageRegistry packages()
 * @method static \Syscage\Engine\Registry\PluginRegistry plugins()
 * @method static \Syscage\Engine\Support\PathResolver paths()
 * @method static \Syscage\Engine\Generator\StubEngine stubs()
 * @method static \Syscage\Engine\Generator\ClassGenerator generator()
 * @method static \Syscage\Engine\Contracts\PackageManifestContract find(string $name)
 * @method static void discoverAll()
 * @method static array discoverDrivers(string $directory, string $namespace, string $interface)
 *
 * @see EngineManager
 */
final class Engine extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EngineManager::class;
    }
}
