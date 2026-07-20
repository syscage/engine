<?php

declare(strict_types=1);

namespace Syscage\Engine\Support;

use Illuminate\Contracts\Config\Repository as ConfigContract;

/**
 * Resolves every filesystem path the SysCage ecosystem cares about,
 * driven entirely by configuration so nothing is ever hardcoded.
 */
final readonly class PathResolver
{
    public function __construct(
        private ConfigContract $config,
        private string $basePath,
    ) {
    }

    /**
     * The application's base path, optionally joined with a suffix.
     */
    public function base(string $path = ''): string
    {
        return $this->join($this->basePath, $path);
    }

    /**
     * The root directory in which plugins are stored (default: `plugins/`).
     */
    public function plugins(string $path = ''): string
    {
        $configured = (string) $this->config->get('engine.plugins_path', 'plugins');

        return $this->join($this->resolveMaybeAbsolute($configured), $path);
    }

    /**
     * The directory a specific plugin lives in, by its manifest name.
     */
    public function plugin(string $name, string $path = ''): string
    {
        return $this->join($this->plugins($name), $path);
    }

    /**
     * The engine's own package root (this package's install directory).
     */
    public function enginePackage(string $path = ''): string
    {
        return $this->join(dirname(__DIR__, 2), $path);
    }

    /**
     * The directory used for user-published stub overrides.
     */
    public function publishedStubs(string $path = ''): string
    {
        $configured = (string) $this->config->get('engine.stubs.publish_path', 'stubs/syscage');

        return $this->join($this->resolveMaybeAbsolute($configured), $path);
    }

    /**
     * The directory used for the engine's own default stubs.
     */
    public function defaultStubs(string $path = ''): string
    {
        return $this->enginePackage('stubs/' . ltrim($path, '/\\'));
    }

    /**
     * The directory used for the engine's discovery/registry cache.
     */
    public function cache(string $path = ''): string
    {
        $configured = (string) $this->config->get('engine.cache.path', 'bootstrap/cache');

        return $this->join($this->resolveMaybeAbsolute($configured), $path);
    }

    private function resolveMaybeAbsolute(string $path): string
    {
        return $this->isAbsolute($path) ? $path : $this->base($path);
    }

    private function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || (bool) preg_match('#^[A-Za-z]:[\\\\/]#', $path);
    }

    private function join(string $base, string $path): string
    {
        if ($path === '') {
            return rtrim($base, '/\\');
        }

        return rtrim($base, '/\\') . '/' . ltrim($path, '/\\');
    }
}
