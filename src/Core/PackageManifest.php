<?php

declare(strict_types=1);

namespace Syscage\Engine\Core;

use Illuminate\Support\Arr;
use Syscage\Engine\Contracts\PackageManifestContract;
use Syscage\Engine\Exceptions\InvalidManifestException;

/**
 * Immutable value object wrapping the decoded contents of a
 * `plugin.json` (or equivalent) manifest file.
 */
final readonly class PackageManifest implements PackageManifestContract
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        private array $attributes,
        private string $basePath,
    ) {
    }

    /**
     * Build a manifest instance from a JSON file on disk.
     */
    public static function fromJsonFile(string $path, string $basePath): self
    {
        if (! is_file($path)) {
            throw InvalidManifestException::notFound($path);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw InvalidManifestException::notFound($path);
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw InvalidManifestException::invalidJson($path, json_last_error_msg());
        }

        return self::fromArray($decoded ?? [], $basePath);
    }

    /**
     * Build a manifest instance from an already decoded array.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(array $attributes, string $basePath): self
    {
        if (! array_key_exists('name', $attributes) || trim((string) $attributes['name']) === '') {
            throw InvalidManifestException::missingKey($basePath, 'name');
        }

        return new self($attributes, rtrim($basePath, '/\\'));
    }

    public function name(): string
    {
        return (string) Arr::get($this->attributes, 'name');
    }

    public function displayName(): string
    {
        $display = Arr::get($this->attributes, 'display_name') ?? Arr::get($this->attributes, 'title');

        return $display !== null ? (string) $display : $this->name();
    }

    public function description(): ?string
    {
        $description = Arr::get($this->attributes, 'description');

        return $description !== null ? (string) $description : null;
    }

    public function version(): string
    {
        return (string) (Arr::get($this->attributes, 'version') ?? '0.0.0');
    }

    public function isEnabled(): bool
    {
        return (bool) (Arr::get($this->attributes, 'enabled') ?? true);
    }

    public function provider(): ?string
    {
        $provider = Arr::get($this->attributes, 'provider');

        return $provider !== null ? (string) $provider : null;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }
}
