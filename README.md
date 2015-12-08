## Panthor

A thin PHP microframework built on Slim and Symfony.

Panthor is a skeleton application that provides sensible defaults with quick installation and
deployment settings and scripts using Symfony Dependency Injection and Slim.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.

Panthor `1.*` and `2.*` are compatible with Slim `2.*` and Symfony `2.*`. Panthor `3.*` will require
Slim `3.*` and Symfony `3.*`.

### Starting a new application?

See the following repositories for example skeletons.

For APIs, see [Panthor API](http://git/web-frameworks/panthor-api).

For applications, see [Panthor Application](http://git/web-frameworks/panthor-app).

### Documentation

- [Application Structure](docs/APPLICATION_STRUCTURE.md)
  > Details on where code and configuration goes.

- [How to use](docs/USAGE.md)
  > Explanations of controllers and middleware, as well as services injected into the Di Container by Panthor.

- [Error Handling](docs/ERRORS.md)
  > How to use the included error handler and logger.

- [Web Server Configuration](docs/SERVER.md)
  > Example setups for nginx and apache.

### Optional Dependencies

This library contains many convenience utilities and classes for your application. Some of this functionality requires
other libraries, but because they are optional, they are not strict requirements.

Please take note of the following packages and include them in your composer `require` if you
use the associated Panthor functionality.

Library / Extension              | Required by
-------------------------------- | -----------
psr/log                          | `Testing\Logger`, `ErrorHandling\`
twig/twig                        | `Twig\`
paragonie/random_compat or PHP7  | `Encryption\`
PECL Libsodium                   | `Encryption\`, `Http\EncryptedCookies\`
