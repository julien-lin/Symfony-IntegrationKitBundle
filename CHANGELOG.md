# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-22

### Added
- Initial release of IntegrationKitBundle
- Interface `IntegrationCommand` for typed commands
- Interface `IntegrationInterface` for integration aggregates
- Interface `IntegrationHandlerInterface` for explicit handlers
- `IntegrationRegistry` and `HandlerRegistry` for service resolution
- `IntegrationExecutor` for execution with instrumentation
- `ApiResult` for standardized results (optional)
- Symfony events: `IntegrationRequestEvent`, `IntegrationSuccessEvent`, `IntegrationFailureEvent`
- `IntegrationLoggerListener` for structured JSON logging
- Optional Messenger support: `IntegrationMessage` and `IntegrationMessageHandler`
- Normalized exceptions: `IntegrationException`, `IntegrationNotFoundException`
- `IntegrationKitExtension` for automatic service registration
- `IntegrationCompilerPass` for automatic discovery of integrations and handlers
- Complete tests: 147 tests, 309 assertions
- Minimal Slack integration example

### Features
- Strong typing everywhere (PHP 8.2+)
- No dynamic reflection
- No magic strings
- Compatible with Symfony 7.0+ and 8.0+
- Optional Messenger (automatic detection)
- Minimal configuration (works without YAML)
- All code comments in English
- Documentation in English and French

[1.0.0]: https://github.com/integration-kit/bundle/releases/tag/v1.0.0
