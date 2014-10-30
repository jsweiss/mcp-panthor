## Panthor

A smaller, more nimble microframework skeleton application using Slim.

Panthor is a skeleton application that provides sensible defaults with quick installation and deployment settings and
scripts using Symfony Dependency Injection and Slim.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.

### Starting a new application?

For APIs, see [Panthor API](http://git/web-frameworks/panthor-api).

For applications, see [Panthor Application](http://git/web-frameworks/panthor-app).

When first setting up an application, we recommend using the composer `create-project` functionality for `app` or `api`. While this is not required to use Panthor, it will automate much of the configuration and get you up and running quicker.

### Documentation

- [Application Structure](docs/APPLICATION_STRUCTURE.md)
  > Details on where code and configuration goes.

- [How to use](docs/USAGE.md)
  > Explanations of controllers and middleware, as well as services injected into the Di Container by Panthor.

- [Web Server Configuration](docs/SERVER.md)
  > Example setups for nginx and apache.

### Optional Dependencies

This library contains many convenience utilities and classes for your application. Some of this functionality requires other libraries, but because they are optional, they are not strict requirements.

Please take note of the following packages and include them in your composer `require` if you use the associated Panthor functionality.

Library         | Required by
--------------- | -----------
psr/log         | `Testing\Logger`
ql/mcp-core     | `Twig\TwigExtension`
ql/mcp-crypto   | `Http\EncryptedCookies`
twig/twig       | `Twig\`

### Development installation

The initial application may be installed with `bin/install && bin/normalize-configuration`.

The file `configuration/config.env.yml` is required for the application to run, as it contains environment-specific
settings. `normalize-configuration` will automatically copy the dev configuration to this file. This file is ignored
in the repository and each developer may customize it for their specific settings. Do not run `normalize-configuration`
more than once, as is it will overwrite your custom development settings.

### Deployment

`bin/deploy` - The included deployment script downloads PHP dependencies, copies environment-based configuration
files, and caches the service container. This script is designed for use with HAL 9000.
