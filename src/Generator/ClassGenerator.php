<?php

declare(strict_types=1);

namespace Syscage\Engine\Generator;

use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\StubRendererContract;

/**
 * Combines a {@see StubRendererContract} with a namespace-to-path
 * mapping to generate a single class file on disk. Every `make:*`
 * command across the SysCage ecosystem should generate files through
 * this class rather than duplicating the namespace/path logic.
 */
final class ClassGenerator
{
    public function __construct(
        private readonly FilesystemContract $files,
        private readonly StubRendererContract $stubs,
    ) {
    }

    /**
     * Generate a class file.
     *
     * @param  string  $stub  Stub name, resolved via the stub renderer.
     * @param  string  $fullyQualifiedClassName  e.g. "Plugin\WhatsApp\Http\Controllers\WebhookController".
     * @param  string  $namespaceRoot  e.g. "Plugin\WhatsApp".
     * @param  string  $pathRoot  Absolute directory the namespace root maps to, e.g. ".../plugins/whatsapp/src".
     * @param  array<string, mixed>  $replacements  Additional stub placeholders beyond namespace/class/name.
     * @param  bool  $force  Overwrite the destination file if it already exists.
     * @return string  The absolute path of the generated file.
     */
    public function generate(
        string $stub,
        string $fullyQualifiedClassName,
        string $namespaceRoot,
        string $pathRoot,
        array $replacements = [],
        bool $force = false,
    ): string {
        $destination = $this->resolvePath($fullyQualifiedClassName, $namespaceRoot, $pathRoot);

        if ($this->files->exists($destination) && ! $force) {
            return $destination;
        }

        $namespace = $this->resolveNamespace($fullyQualifiedClassName);
        $class = $this->resolveClassName($fullyQualifiedClassName);

        $contents = $this->stubs->render($this->stubs->resolveStubPath($stub), array_merge(
            $replacements,
            [
                'namespace' => $namespace,
                'class' => $class,
                'class_name' => $class,
            ],
        ));

        $this->files->ensureDirectoryExists(dirname($destination));
        $this->files->put($destination, $contents);

        return $destination;
    }

    /**
     * Compute the absolute destination path for a FQCN given a
     * namespace root and the path that root maps to (PSR-4 style).
     */
    public function resolvePath(string $fullyQualifiedClassName, string $namespaceRoot, string $pathRoot): string
    {
        $namespaceRoot = trim($namespaceRoot, '\\');
        $fullyQualifiedClassName = trim($fullyQualifiedClassName, '\\');

        $relative = str_starts_with($fullyQualifiedClassName, $namespaceRoot)
            ? ltrim(substr($fullyQualifiedClassName, strlen($namespaceRoot)), '\\')
            : $fullyQualifiedClassName;

        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

        return rtrim($pathRoot, '/\\') . DIRECTORY_SEPARATOR . $relativePath;
    }

    private function resolveNamespace(string $fullyQualifiedClassName): string
    {
        $trimmed = trim($fullyQualifiedClassName, '\\');
        $position = strrpos($trimmed, '\\');

        return $position === false ? '' : substr($trimmed, 0, $position);
    }

    private function resolveClassName(string $fullyQualifiedClassName): string
    {
        $trimmed = trim($fullyQualifiedClassName, '\\');
        $position = strrpos($trimmed, '\\');

        return $position === false ? $trimmed : substr($trimmed, $position + 1);
    }
}
