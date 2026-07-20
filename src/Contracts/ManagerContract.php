<?php

declare(strict_types=1);

namespace Syscage\Engine\Contracts;

/**
 * Implemented by classes following the Manager pattern: they resolve
 * and cache "driver" implementations of some capability (e.g. payment
 * gateways, chatbot providers, notification channels) by name.
 */
interface ManagerContract
{
    /**
     * Resolve a driver instance by name, using the default driver
     * when no name is given.
     */
    public function driver(?string $name = null): mixed;

    /**
     * Register a custom driver resolver at runtime.
     */
    public function extend(string $driver, \Closure $callback): static;

    /**
     * The name of the default driver to use when none is specified.
     */
    public function getDefaultDriver(): string;
}
