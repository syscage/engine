# Contributing to syscage/engine

Thanks for considering a contribution to the SysCage Engine!

## Development setup

```bash
git clone https://github.com/syscage/engine.git
cd engine
composer install
```

## Running tests

```bash
composer test
# or directly:
vendor/bin/phpunit
```

## Coding standards

This package follows PSR-1, PSR-4, and PSR-12, uses strict types in every file,
and relies on constructor property promotion and readonly properties wherever
appropriate. Please run the test suite (and add tests for any new behaviour)
before opening a pull request.

## Pull requests

1. Fork the repository and create a feature branch off `main`.
2. Keep pull requests focused — one logical change per PR.
3. Include or update tests for any behavioural change.
4. Update `CHANGELOG.md` under the `[Unreleased]` section.
5. Ensure `vendor/bin/phpunit` passes locally before submitting.

## Reporting issues

Please include:

- The package version and PHP/Laravel version in use.
- Steps to reproduce, including a minimal code sample where possible.
- The behaviour you expected versus what actually happened.

## Security vulnerabilities

Please do not open a public issue for security vulnerabilities. Instead,
report them privately as described in `README.md`.
