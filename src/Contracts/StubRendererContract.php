<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * Renders stub templates by replacing placeholders and evaluating
 * conditional sections, used by every `make:*` generator command
 * across the SysCage ecosystem.
 */
interface StubRendererContract
{
    /**
     * Render the contents of a stub file, replacing placeholders and
     * resolving conditional blocks.
     *
     * @param  string  $stubPath  Absolute path to the stub file.
     * @param  array<string, mixed>  $replacements  Placeholder => value pairs.
     *                                               Boolean values additionally
     *                                               control conditional sections
     *                                               sharing the same key.
     */
    public function render(string $stubPath, array $replacements = []): string;

    /**
     * Render a stub given as a raw string rather than a file path.
     *
     * @param  array<string, mixed>  $replacements
     */
    public function renderString(string $contents, array $replacements = []): string;

    /**
     * Resolve the absolute path to a named stub, honouring any
     * user-published overrides before falling back to package stubs.
     */
    public function resolveStubPath(string $name): string;
}
