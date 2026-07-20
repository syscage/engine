<?php

declare(strict_types=1);

namespace Syscage\Engine\Filesystem;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Syscage\Engine\Contracts\FilesystemContract;

/**
 * Wraps Illuminate's filesystem component behind the engine's own
 * contract, so generators and discovery classes depend on an
 * interface rather than a concrete Laravel class.
 */
final class Filesystem implements FilesystemContract
{
    public function __construct(
        private readonly IlluminateFilesystem $files = new IlluminateFilesystem(),
    ) {
    }

    public function exists(string $path): bool
    {
        return $this->files->exists($path);
    }

    public function get(string $path): string
    {
        return $this->files->get($path);
    }

    public function put(string $path, string $contents): bool
    {
        $this->ensureDirectoryExists(dirname($path));

        return $this->files->put($path, $contents) !== false;
    }

    public function ensureDirectoryExists(string $path, int $mode = 0755): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, $mode, true, true);
        }
    }

    public function glob(string $pattern): array
    {
        $matches = glob($pattern);

        return $matches === false ? [] : $matches;
    }

    public function copyDirectory(string $source, string $destination): bool
    {
        $this->ensureDirectoryExists($destination);

        return $this->files->copyDirectory($source, $destination);
    }

    public function isDirectory(string $path): bool
    {
        return $this->files->isDirectory($path);
    }

    public function isFile(string $path): bool
    {
        return $this->files->isFile($path);
    }
}
