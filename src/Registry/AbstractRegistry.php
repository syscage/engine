<?php

declare(strict_types=1);

namespace Syscage\Engine\Registry;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Contracts\RegistryContract;
use Syscage\Engine\Events\PackageRegistered;

/**
 * Shared array-backed implementation of {@see RegistryContract}.
 * Concrete registries only need to exist to be semantically distinct
 * bindings (packages vs. plugins vs. drivers) — all storage logic
 * lives here.
 */
abstract class AbstractRegistry implements RegistryContract
{
    /**
     * @var array<string, PackageManifestContract>
     */
    protected array $items = [];

    public function __construct(
        protected readonly ?Dispatcher $events = null,
    ) {
    }

    public function register(PackageManifestContract $manifest): static
    {
        $this->items[$manifest->name()] = $manifest;

        $this->events?->dispatch(new PackageRegistered($manifest, static::class));

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->items);
    }

    public function get(string $name): ?PackageManifestContract
    {
        return $this->items[$name] ?? null;
    }

    public function forget(string $name): static
    {
        unset($this->items[$name]);

        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function enabled(): array
    {
        return array_filter($this->items, static fn (PackageManifestContract $manifest): bool => $manifest->isEnabled());
    }

    public function count(): int
    {
        return count($this->items);
    }
}
