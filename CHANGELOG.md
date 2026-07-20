# Changelog

All notable changes to `syscage/engine` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- `StubEngine::resolveConditionals()` threw an undefined-variable error on
  `{{#if}}` blocks because the callback closure was missing `use ($replacements)`.
- `PathResolver` produced backslash-separated paths on Windows, causing joined
  paths (e.g. `plugins()`, `base()`) to mix separators; path joining now
  always uses forward slashes, which PHP accepts on every platform.

### Added
- Initial release of the SysCage Engine foundation package.
- Package and plugin registries (`PackageRegistry`, `PluginRegistry`) built on a
  shared `AbstractRegistry`.
- Discovery strategies: `PluginDiscovery` (scans `plugins/*/plugin.json`),
  `PackageDiscovery` (scans `vendor/composer/installed.json` for `syscage/*`
  packages), and `DriverDiscovery` (finds classes implementing a given
  interface for driver-style extension points).
- `StubEngine` supporting placeholder replacement and `{{#if}}` / `{{#unless}}`
  conditional sections, with user-publishable stub overrides.
- `ClassGenerator` for mapping a fully-qualified class name onto a filesystem
  path and writing a rendered stub there.
- `AbstractManager`, a reusable Manager-pattern base class for dependent
  packages that expose driver-based APIs (payment gateways, chatbot providers,
  notification channels, etc.).
- `PathResolver` centralising every SysCage-related filesystem path,
  fully configuration-driven.
- `engine:discover` and `engine:list` Artisan commands.
- `Engine` facade exposing `EngineManager`.
