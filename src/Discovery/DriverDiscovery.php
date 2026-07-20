<?php

declare(strict_types=1);

namespace Syscage\Engine\Discovery;

use Illuminate\Contracts\Events\Dispatcher;
use ReflectionClass;
use ReflectionException;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Core\PackageManifest;

/**
 * Scans a directory that maps onto a PSR-4 namespace and returns a
 * manifest for every already-autoloadable class within it that
 * implements a given interface. Used by dependent packages (plugin,
 * payment, chatbot, ...) to discover "driver" implementations such as
 * payment gateways or notification channels without any manual
 * registration step.
 */
final class DriverDiscovery extends AbstractDiscovery
{
    public function __construct(
        FilesystemContract $files,
        private readonly string $directory,
        private readonly string $namespace,
        private readonly string $interface,
        ?Dispatcher $events = null,
    ) {
        parent::__construct($files, $events);
    }

    public function identifier(): string
    {
        return 'driver:' . class_basename($this->interface);
    }

    protected function scan(): iterable
    {
        if (! $this->files->isDirectory($this->directory)) {
            return [];
        }

        $files = $this->files->glob(rtrim($this->directory, '/\\') . DIRECTORY_SEPARATOR . '*.php');

        $classes = [];

        foreach ($files as $file) {
            $relative = ltrim(str_replace($this->directory, '', $file), '/\\');
            $relative = str_replace(['/', '\\'], '\\', $relative);
            $class = rtrim($this->namespace, '\\') . '\\' . str_replace('.php', '', $relative);

            if (! class_exists($class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);
            } catch (ReflectionException) {
                continue;
            }

            if (! $reflection->isInstantiable()) {
                continue;
            }

            if (! $reflection->implementsInterface($this->interface)) {
                continue;
            }

            $classes[] = $reflection;
        }

        return $classes;
    }

    protected function buildManifest(mixed $source): ?PackageManifestContract
    {
        /** @var ReflectionClass<object> $reflection */
        $reflection = $source;

        return PackageManifest::fromArray([
            'name' => \Illuminate\Support\Str::snake(class_basename($reflection->getName())),
            'display_name' => class_basename($reflection->getName()),
            'description' => null,
            'version' => '0.0.0',
            'enabled' => true,
            'provider' => null,
            'class' => $reflection->getName(),
        ], (string) $reflection->getFileName());
    }
}
