## Panthor

A smaller, more nimble microframework skeleton application using Slim.

Panthor is a skeleton application that provides sensible defaults with quick installation and deployment settings and
scripts using Symfony Dependency Injection and Slim.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.

### Documentation

- [Application Structure](docs/APPLICATION_STRUCTURE.md)
  > Details on where code and configuration goes.
- [How to use](docs/USAGE.md)
  > Explanations of controllers and middleware, as well as services injected into the Di Container by Panthor.
- [Web Server Configuration](docs/SERVER.md)
  > Example setups for nginx and apache.

### Development installation

The initial application may be installed with `bin/install && bin/normalize-configuration`.

The file `configuration/config.env.yml` is required for the application to run, as it contains environment-specific
settings. `normalize-configuration` will automatically copy the dev configuration to this file. This file is ignored
in the repository and each developer may customize it for their specific settings. Do not run `normalize-configuration`
more than once, as is it will overwrite your custom development settings.

### Deployment

`bin/deploy` - The included deployment script downloads PHP dependencies, copies environment-based configuration
files, and caches the service container. This script is designed for use with HAL 9000.
