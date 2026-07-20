<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * A registry keeps track of a keyed collection of manifests
 * (packages, plugins, drivers, ...) and exposes basic CRUD-style
 * lookups over that collection.
 */
interface RegistryContract
{
    /**
     * Register a manifest under its own name. Overwrites any
     * previously registered manifest with the same name.
     */
    public function register(PackageManifestContract $manifest): static;

    /**
     * Determine whether an entry with the given name has been registered.
     */
    public function has(string $name): bool;

    /**
     * Retrieve a registered manifest by name.
     */
    public function get(string $name): ?PackageManifestContract;

    /**
     * Remove a registered manifest by name.
     */
    public function forget(string $name): static;

    /**
     * All registered manifests, keyed by name.
     *
     * @return array<string, PackageManifestContract>
     */
    public function all(): array;

    /**
     * All registered manifests that are currently enabled.
     *
     * @return array<string, PackageManifestContract>
     */
    public function enabled(): array;

    /**
     * Total number of registered entries.
     */
    public function count(): int;
}
