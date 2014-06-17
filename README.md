## Panthor

A smaller, more nimble microframework skeleton application using Slim.

Panthor is a skeleton application that provides sensible defaults with quick installation and deployment settings and
scripts using Symfony Dependency Injection and Slim.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.


### How to use it

#### On deployment:

`bin/deploy` - The included deployment script downloads PHP dependencies, copies environment-based configuration
files, and caches the service container. This script is designed for use with HAL 9000.

#### For development:

The initial application may be installed with `bin/install && bin/normalize-configuration`. Do not run
`normalize-configuration` more than once, as is it will overwrite your custom development settings.
