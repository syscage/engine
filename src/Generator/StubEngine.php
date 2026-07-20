<?php

declare(strict_types=1);

namespace Syscage\Engine\Generator;

use Illuminate\Support\Arr;
use Syscage\Engine\Contracts\FilesystemContract;
use Syscage\Engine\Contracts\StubRendererContract;
use Syscage\Engine\Exceptions\StubNotFoundException;
use Syscage\Engine\Support\PathResolver;

/**
 * Generic stub rendering engine shared by every `make:*` generator
 * across the SysCage ecosystem.
 *
 * Supported syntax:
 *   {{ variable }}                simple placeholder replacement
 *   {{ nested.value }}            dot-notation lookup
 *   {{#if flag}} ... {{/if}}      conditional section, truthy flag
 *   {{#if flag}} A {{else}} B {{/if}}  conditional with else branch
 *   {{#unless flag}} ... {{/unless}}  inverse conditional section
 *
 * Stub resolution searches, in order: published user overrides,
 * any additional paths registered at runtime by dependent packages,
 * then the engine's own bundled default stubs.
 */
final class StubEngine implements StubRendererContract
{
    private const string IF_PATTERN = '/\{\{#if\s+([\w.]+)\}\}(.*?)(?:\{\{else\}\}(.*?))?\{\{\/if\}\}/s';

    private const string UNLESS_PATTERN = '/\{\{#unless\s+([\w.]+)\}\}(.*?)\{\{\/unless\}\}/s';

    private const string PLACEHOLDER_PATTERN = '/\{\{\s*([\w.]+)\s*\}\}/';

    /**
     * @var array<int, string>
     */
    private array $searchPaths = [];

    public function __construct(
        private readonly FilesystemContract $files,
        PathResolver $paths,
    ) {
        $this->searchPaths[] = $paths->publishedStubs();
        $this->searchPaths[] = $paths->defaultStubs();
    }

    /**
     * Register an additional stub search path (used by dependent
     * packages to expose their own bundled stub directories).
     */
    public function registerPath(string $path, bool $prepend = false): static
    {
        if ($prepend) {
            array_unshift($this->searchPaths, $path);
        } else {
            // Keep default engine stubs last regardless of registration order.
            array_splice($this->searchPaths, count($this->searchPaths) - 1, 0, [$path]);
        }

        return $this;
    }

    public function resolveStubPath(string $name): string
    {
        $filename = str_ends_with($name, '.stub') ? $name : $name . '.stub';

        foreach ($this->searchPaths as $searchPath) {
            $candidate = rtrim($searchPath, '/\\') . DIRECTORY_SEPARATOR . $filename;

            if ($this->files->isFile($candidate)) {
                return $candidate;
            }
        }

        throw StubNotFoundException::named($name);
    }

    public function render(string $stubPath, array $replacements = []): string
    {
        if (! $this->files->isFile($stubPath)) {
            throw StubNotFoundException::atPath($stubPath);
        }

        return $this->renderString($this->files->get($stubPath), $replacements);
    }

    public function renderString(string $contents, array $replacements = []): string
    {
        $contents = $this->resolveConditionals($contents, $replacements);

        return $this->replacePlaceholders($contents, $replacements);
    }

    /**
     * @param  array<string, mixed>  $replacements
     */
    private function resolveConditionals(string $contents, array $replacements): string
    {
        $contents = (string) preg_replace_callback(
            self::UNLESS_PATTERN,
            fn (array $matches): string => $this->isTruthy($replacements, $matches[1]) ? '' : $matches[2],
            $contents,
        );

        return (string) preg_replace_callback(
            self::IF_PATTERN,
            function (array $matches) use ($replacements): string {
                $truthy = $this->isTruthy($replacements, $matches[1]);
                $elseBranch = $matches[3] ?? '';

                return $truthy ? $matches[2] : $elseBranch;
            },
            $contents,
        );
    }

    /**
     * @param  array<string, mixed>  $replacements
     */
    private function replacePlaceholders(string $contents, array $replacements): string
    {
        return (string) preg_replace_callback(
            self::PLACEHOLDER_PATTERN,
            static function (array $matches) use ($replacements): string {
                $value = Arr::get($replacements, $matches[1], '');

                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }

                if (is_array($value)) {
                    return implode(', ', $value);
                }

                return (string) $value;
            },
            $contents,
        );
    }

    /**
     * @param  array<string, mixed>  $replacements
     */
    private function isTruthy(array $replacements, string $key): bool
    {
        return (bool) Arr::get($replacements, $key, false);
    }
}
