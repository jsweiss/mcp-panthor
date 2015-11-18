# Change Log
All notable changes to this project will be documented in this file. See [keepachangelog.com](http://keepachangelog.com) for reference.

## [2.3.0] - 2015-??-??

Please note: This release has backwards compatibility breaks to remove links to proprietary packages.

### Removed
- Remove `QL\Panthor\Slim\McpLoggerHook`
    - Please see **MCPLoggerHook** in `ql/panthor-plugins`.
- Remove `timepoint` filter from **TwigExtension**.
    - Please see **TwigExtension** in `ql/panthor-plugins`.
- Remove `timepoint` function from **TwigExtension**.
    - Please see **TwigExtension** in `ql/panthor-plugins`.
- DI: Remove `@clock` service.

### Changed
- **ApacheAuthorizationHeaderHook** is deprecated and will be removed in 3.0.

## [2.2.0] - 2015-07-27

### Added
- Add `QL\Panthor\ErrorHandling\ErrorHandler`
    - Logs and renders errors to Slim.
- Add `QL\Panthor\ErrorHandling\FatalErrorHandler`
    - Converts Fatal Errors to Exceptions through *ShutdownHandler*.
- Add Exception Configurators
    - Exception configurators allow filtering of exceptions through different handlers, which can render exceptions differently.
    - Example: An application with an API may want to render exceptions thrown by API controllers differently than exceptions thrown by html controllers.
    - See `QL\Panthor\ErrorHandling\APIExceptionConfigurator`
    - See `QL\Panthor\ErrorHandling\ExceptionConfigurator`
- Logging
    - Add `QL\Panthor\Slim\McpLoggerHook` for adding request parameters to logger defaults.

## [2.1.0] - 2015-06-29

### Added
- **TRPCookieEncryption** added for libsodium-based cookie encryption (preferred).
- **AESCookieEncryption** added for mcrypt-based cookie encryption.
    - This is the default, for backwards compatibility.

### Changed
- Encrypted Cookies
    - Encrypted Cookies now requires **mcp-crypto** `2.*`.
    - Cookies are now automatically json encoded/decoded.
    - DI: `%encryption.secret%` changed to `%cookie.encryption.secret%`
    - DI: Added `%cookie.unencrypted%` to allow a whitelist of cookie names that will not be encrypted.
- DI Configuration moved from `configuration/di.yml` to `configuration/panthor.yml`

## [2.0.4] - 2015-06-02

### Changed
- Restrict **mcp-core** to `1.*`.
- Restrict **mcp-crypto** to `1.*`.
- Remove `QL\Panthor\Bootstrap\RouteLoaderHook` that was intended to be removed in 2.0.
    - Please see `QL\Panthor\Slim\RouteLoaderHook` instead.

### Added
- **RouteLoaderHook** can now load routes from multiple sources.
    - Added `RouteLoaderHook::addRoutes(array $routes)` which can be called multiple times.

## [2.0.3] - 2015-03-24

### Changed
- **symfony/dependency-injection** >= `2.6.3` now required.
- DI: Factory services have been updated to use new `factory` syntax.

## [2.0.2] - 2015-02-12

### Fixed
- `QL\Panthor\Twig\BetterCachingFilesystem` now correctly resolves relative template file paths.

## [2.0.1] - 2015-01-20
- A callable can be passed to `QL\Panthor\Bootstrap\Di::buildDi` when building the container to modify services or parameters before compilation.
    - This can be used to inject parameters from the environment `_SERVER`.

## [2.0.0] - 2015-01-12

### Added
- DI: **slim.not.found** service added.
    - Similar to `slim.halt`, a convenience wrapper for `Slim::notFound` is provided by `slim.not.found`.

### Removed
- The Route Loader no longer injects the following di services.
    - `slim.environment`
    - `slim.request`
    - `slim.response`
    - `slim.parameters`
    - `slim.halt`

### Changed
- DI: **slim.halt** service is no longer set as a callable closure.
    - It will now be an instance of `QL\Panthor\Slim\Halt`, but it is still callable. If you typehinted to callable, no changes need to be made.
- DI: **slim.parameters** is replaced by **slim.route.parameters**.
