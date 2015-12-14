# MCP Panthor

A thin PHP microframework built on Slim and Symfony.

Slim + Symfony = :revolving_hearts:

Panthor is a skeleton application that provides sensible defaults with quick installation and
deployment settings and scripts using Symfony Dependency Injection and Slim. It can be used for html
applications, APIs, or both.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.

## Table of Contents

- [Compatibility](#compatibility)
- [Starting a new application?](#starting-a-new-application)
    - [Installation](#installation)
    - [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Dependencies](#dependencies)
    - [Optional Dependencies](#optional-dependencies)

## Compatibility

- **Panthor 1**
    - Slim ~2.0
    - Symfony ~2.0
- **Panthor 2**
    - Slim ~2.0
    - Symfony ~2.0
- Panthor 3 (roadmap)
    - Slim ~3.0
    - Symfony ~3.0

## Starting a new application?

#### Installation

```
composer require ql/mcp-panthor ~2.3
```

See the following repositories for example skeletons.

For APIs, see [Panthor API](http://git/web-frameworks/panthor-api).

For applications, see [Panthor Application](http://git/web-frameworks/panthor-app).

Never used Composer, Slim or Symfony before? Here are some resources:
- [Composer - Getting Started](https://getcomposer.org/doc/00-intro.md)
- [Symfony Book - Service Container](http://symfony.com/doc/current/book/service_container.html)
- [Slim Framework v2 documentation](http://docs.slimframework.com/)

#### Quick Start

1. Create an application with the following file hiearchy:

   > ```
   > configuration/
   >     bootstrap.php
   >     config.yml
   >     di.yml
   >     routes.yml
   > public/
   >     index.php
   > src/
   >     TestController.php
   > ```

2. Initialize composer with the following commands:

   > ```
   > composer init
   > composer require ql/mcp-panthor ~2.3 paragonie/random_compat ~1.1
   >
   > // Also require twig/twig if using html templating
   > composer require twig/twig ~1.20
   > ```

3. `config.yml` should import other config resources.

    > ```yaml
    > imports:
    >     - resource: ../vendor/ql/mcp-panthor/configuration/panthor.yml
    >     - resource: di.yml
    >     - resource: routes.yml
    > ```

4. `di.yml` will contain service definitions for your application, such as controllers.

    > ```yaml
    > services:
    >     # Reset slim response to not use encryption for cookies
    >     slim.response:
    >         class: 'Slim\Http\Response'
    >
    >     page.hello_world:
    >         class: 'TestApplication\TestController'
    >         arguments:
    >             - '@slim.response'
    > ```

5. `routes.yml` contains routes.

    > Routes is simply another config parameter passed into the DI container. It maps a route name to a url and list of
    > services to call.
    > ```yaml
    > parameters:
    >     routes:
    >         hello_world:
    >             route: '/'
    >             stack: ['page.hello_world']
    > ```

6. `bootstrap.php` should load the composer autoloader and return the DI container.

    > ```php
    > <?php
    >
    > namespace TestApplication\Bootstrap;
    >
    > use QL\Panthor\Bootstrap\Di;
    > use TestApplication\CachedContainer;
    >
    > $root = __DIR__ . '/..';
    > require_once $root . '/vendor/autoload.php';
    >
    > return Di::getDi($root, CachedContainer::class);
    > ```

7. `index.php` loads the bootstrap and starts **Slim**.

    > ```php
    > <?php
    >
    > namespace TestApplication\Bootstrap;
    >
    > if (!$container = @include __DIR__ . '/../configuration/bootstrap.php') {
    >     http_response_code(500);
    >     echo "The application failed to start.\n";
    >     exit;
    > };
    >
    > $container->get('slim')->run();
    > ```

8. `TestController.php` is a simple class that can be **invoked** as a callable.

    > ```php
    >
    > <?php
    >
    > namespace TestApplication;
    >
    > use QL\Panthor\ControllerInterface;
    > use Slim\Http\Response;
    >
    > class TestController implements ControllerInterface
    > {
    >     private $response;
    >
    >     public function __construct(Response $response)
    >     {
    >         $this->response = $response;
    >     }
    >
    >     public function __invoke()
    >     {
    >         $this->response->setBody('Hello World!');
    >     }
    > }
    > ```

8. Don't forget your web server configuration!
   - Panthor is just Slim under the hood, so it uses the same NGINX or Apache configuration as Slim (standard
     `index.php` rewrite).

Now just visit `localhost` (or your preferred virtual host name) and your controller should load!

This quickstart leaves out many things such as **Twig Templating**, **Cookie Encryption**, and **Error Handling**.
Check the documentation links below for further details.

## Documentation

- [Application Structure](docs/APPLICATION_STRUCTURE.md)
  > Details on where code and configuration goes.

- [How To Use](docs/USAGE.md)
  > Explanations of controllers and middleware, as well as services injected into the Di Container by Panthor.

- [Error Handling](docs/ERRORS.md)
  > How to use the included error handler and logger.

- [Web Server Configuration](docs/SERVER.md)
  > Example setups for nginx and apache.

## Dependencies

This library contains many convenience utilities and classes for your application. Some of this functionality requires
other libraries, but because they are optional, they are not strict requirements.

Library / Extension              | Used by
-------------------------------- | -----------
slim/slim                        |
symfony/config                   | `Bootstrap\`
symfony/dependency-injection     | `Bootstrap\`
symfony/yaml                     | `Bootstrap\`
psr/log                          | `Testing\Logger`, `ErrorHandling\`
ql/mcp-common                    | `Encryption\`, `Twig\`

### Optional Dependencies

Please take note of the following packages and include them in your composer `require` if you
use the associated Panthor functionality.

Library / Extension              | Required for
-------------------------------- | -----------
twig/twig                        | `Twig\`
paragonie/random_compat or PHP7  | `Encryption\`
PECL Libsodium                   | `Encryption\`, `Http\EncryptedCookies\`
