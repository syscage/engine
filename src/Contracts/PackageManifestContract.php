<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * Represents the parsed manifest of a SysCage package or plugin
 * (e.g. a `plugin.json` file or a `composer.json` entry).
 */
interface PackageManifestContract
{
    /**
     * The unique machine name of the package (e.g. "whatsapp").
     */
    public function name(): string;

    /**
     * The human readable display name (e.g. "WhatsApp").
     */
    public function displayName(): string;

    /**
     * A short description of the package.
     */
    public function description(): ?string;

    /**
     * The semantic version string of the package.
     */
    public function version(): string;

    /**
     * Whether the package is currently enabled.
     */
    public function isEnabled(): bool;

    /**
     * Fully qualified class name of the package's service provider, if any.
     */
    public function provider(): ?string;

    /**
     * The absolute base path the manifest was loaded from.
     */
    public function basePath(): string;

    /**
     * The raw attributes backing this manifest.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Retrieve an arbitrary attribute from the manifest by dot notation key.
     */
    public function get(string $key, mixed $default = null): mixed;
}
