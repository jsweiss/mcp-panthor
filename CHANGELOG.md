# Change Log
All notable changes to this project will be documented in this file. See [keepachangelog.com](http://keepachangelog.com)
for reference.

## [2.4.0] - 2016-03-24

### Changed
- **Error Handling**
    - **ErrorHandler** now supports PHP 7 and **throwable errors**.
    - Added **HandledExceptionsTrait** to typecheck for a handler's ability to handle an exception or PHP 7 **throwable**.
    - **Note:** ExceptionHandlerInterface has changed, if you wrote your own handler it must be updated.
        - See [ExceptionHandlerInterface.php](src/ErrorHandling/ExceptionHandlerInterface.php)
        - See [NotFoundHandler.php](src/ErrorHandling/ExceptionHandler/NotFoundHandler.php) for an example.

## [2.3.1] - 2016-01-12

### Changed
- DI Service `@panthor.error_handling.html_renderer.twig` now uses **TwigTemplate** instead of **LazyTwig**.
- In **BaseHandler**, errors are now logged before attempting to render a response.

### Added
- Add `QL\Panthor\Templating\TwigTemplate`
    - This is a non-lazy version of **LazyTwig**.
    - It should be used for twig rendering during error handling, as lazy loading is more error-prone.

## [2.3.0] - 2015-12-15

Please note: This release has backwards compatibility breaks to remove links to proprietary packages.

### Removed
- Remove `QL\Panthor\Slim\McpLoggerHook`
    - Please see **MCPLoggerHook** in `ql/panthor-plugins`.
- Remove **ApacheAuthorizationHook**
    - This includes removal of `@slim.hook.apacheAuthorization` service.
- **Error Handling**
    - Removed **APIExceptionConfigurator**.
    - Removed **ExceptionConfigurator**.
    - Removed **FatalErrorHandler**.
- **HTTP Problem**
    - Remove usage of `ql/http-problem`, replaced by simple implementation in `QL\Panthor\HTTPProblem` namespace.
- **Encryption**
    - Remove `QL\Panthor\CookieEncryption\AESCookieEncryption`.
    - Remove `QL\Panthor\CookieEncryption\TRPCookieEncryption`.
        - Please see **TRPCookieEncryption** in `ql/panthor-plugins`.

### Changed
- **Error Handling**
    - Errors and exceptions are now handled by a single handler - `QL\Panthor\ErrorHandling\ErrorHandler`.
      > This handler will turn errors into exceptions, which are routed through a list of **exception handlers**.
      > Errors not thrown can be optionally logged, and allow application execution to continue.
    - Exception handlers implement **ExceptionHandlerInterface**, and can determine whether they will handle
      an exception.
      > If not handled by any handler, the exception will be rethrown to be handled by default PHP mechanisms.
- **HTTP Problem**
    - `QL\Panthor\HTTPProblem\HTTPProblem` replaces `QL\HttpProblem\HttpProblem`.

### Added
- **Error Handling**
    - Added exception handlers:
        - **BaseHandler**
            - Should always be used, and attached last. This is the last defense from an unhandled exception, and all
              exceptions that reach this handler will be logged.
        - **GenericHandler**
        - **NullHandler**
        - **HTTPProblemHandler**
        - **RequestExceptionHandler**
    - Added exception renderers:
        - **HTMLRenderer**
            - Passes error context to twig template, by default `error.html.twig` in template directory.
        - **ProblemRenderer**
            - Renders exceptions as http-problem, by default as json.
    - Added **ProtectErrorHandlerMiddleware**
        - This middleware is attached to Slim, and resets the error handler, since Slim 2.x forces its own handler
          when run.
- **Crypto**
    - Added LibsodiumSymmetricCrypto for libsodium-based authenticated symmetric encryption.
    - This is used for cookie encryption, and is the only encryption protocol provided with this library.

## [2.2.0] - 2015-07-27

### Added
- Add `QL\Panthor\ErrorHandling\ErrorHandler`
    - Logs and renders errors to Slim.
- Add `QL\Panthor\ErrorHandling\FatalErrorHandler`
    - Converts Fatal Errors to Exceptions through *ShutdownHandler*.
- Add Exception Configurators
    - Exception configurators allow filtering of exceptions through different handlers, which can render
      exceptions differently.
    - Example: An application with an API may want to render exceptions thrown by API controllers differently
      than exceptions thrown by html controllers.
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
- A callable can be passed to `QL\Panthor\Bootstrap\Di::buildDi` when building the container to modify services or
  parameters before compilation.
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
    - It will now be an instance of `QL\Panthor\Slim\Halt`, but it is still callable. If you typehinted to callable,
      no changes need to be made.
- DI: **slim.parameters** is replaced by **slim.route.parameters**.

## [1.1.0] - 2014-11-03

### Added
- **Dependency Injection**
    - Add **DI** convenience class for bootstrapping and caching DI container during build process.
    - Add included DI configuration to simplify app configuration.
        - Usage: include the following in application `config.yml`:
          ```resource: ../vendor/ql/panthor/configuration/di.yml```
- **Middleware and Hooks**
    - Add **ApacheAuthorizationHeaderHook** to restore lost Authorization header in default apache installations.
    - Add **RequestBodyMiddleware** to automatically decode form, json or multipart POST body requests.
        - This allows applications to simulatenously support multiple media types for POST data.
- **HTTP**
    - Add **EncryptedCookies** to encrypt cookies through QL Crypto library.
- **Utilities**
    - Add **Json** utility to wrap json encoding and decoding in OOP.
    - Add **Stringify** utilityto assist in building complex scalars for usage in symfony configuration.
    - Add **Url** utility to handle slim-related URL functions such as building URLs and redirecting.
- **Templating**
    - Add `QL\Panthor\TemplateInterface` to abstract template implementations from Controllers.
    - Add **BetterCachingFileSystem**.
        - This replacement for the default twig filesystem caches based on template file path
          **relative to application root**. This allows cached templates to be generated on a build server,
          separate from the application web server.
    - Add **Context** to allow adding of context incrementally over the life of a request, through middleware
      or other means.
    - Add **LazyTwig** which defers loading of template files until `render` is called.
    - Add **TwigExtension** for date and url convenience functions.
- **Testing**
    - Add **TestLogger** for an in-memory PSR-3 logger usable by tests.
    - Add **TestResponse** to stringify slim responses, useful for test fixtures.
    - Add **Spy** to add spy functionality to Mockery.
    - Add **MockeryAssistantTrait** for Spy convenience methods.

### Changed
- **RouteLoaderHook** has been moved to `QL\Panthor\Slim\RouteLoaderHook`
    - The existing class is deprecated and will be removed in the next major version.

## [1.0.1] - 2014-10-29

### Removed
- The sample application has been removed.
    - See `ql/panthor-app` or `ql/panthor-api` for example skeletons.

## [1.0.0] - 2014-06-20

Initial release of Panthor.

Panthor is a thin microframework built on top of Slim and Symfony components. It combines Slim with
Symfony DI and Twig. Routes and page middleware and organized into "stacks" and defined by yaml configuration
within Symfony DI configuration.

It is designed to be equally usable for full html web applications, RESTful APIs, or applications that combine both.
