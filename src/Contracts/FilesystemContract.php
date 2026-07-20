<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * Thin, testable abstraction over filesystem operations needed by
 * generators and discovery classes. Keeps Illuminate\Filesystem
 * concerns behind an interface so callers can be mocked in tests.
 */
interface FilesystemContract
{
    public function exists(string $path): bool;

    public function get(string $path): string;

    public function put(string $path, string $contents): bool;

    /**
     * Recursively ensure a directory exists, creating it if necessary.
     */
    public function ensureDirectoryExists(string $path, int $mode = 0755): void;

    /**
     * Find all files matching a glob-style pattern.
     *
     * @return array<int, string>
     */
    public function glob(string $pattern): array;

    /**
     * Copy an entire directory tree from source to destination.
     */
    public function copyDirectory(string $source, string $destination): bool;

    public function isDirectory(string $path): bool;

    public function isFile(string $path): bool;
}
