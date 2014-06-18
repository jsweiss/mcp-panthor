## Panthor

A smaller, more nimble microframework skeleton application using Slim.

Panthor is a skeleton application that provides sensible defaults with quick installation and deployment settings and
scripts using Symfony Dependency Injection and Slim.

- `Slim/Slim` - The core microframework.
- `Symfony/Config` - Cascading configuration to handle merging multiple config files.
- `Symfony/DependencyInjection` - A robust and flexible dependency injection container.

### Development installation

The initial application may be installed with `bin/install && bin/normalize-configuration`.

The file `configuration/config.env.yml` is required for the application to run, as it contains environment-specific
settings. `normalize-configuration` will automatically copy the dev configuration to this file. This file is ignored
in the repository and each developer may customize it for their specific settings. Do not run `normalize-configuration`
more than once, as is it will overwrite your custom development settings.

### Deployment

`bin/deploy` - The included deployment script downloads PHP dependencies, copies environment-based configuration
files, and caches the service container. This script is designed for use with HAL 9000.

### Controllers and middleware

Routes must be given a "stack". This is a list of services retrieved from the service container that the framework
will call in order. The last entry in the stack is the controller. All other services are referred to as "middleware".

There are no restrictions for what a controller or middleware looks like, but they must be `callable`. Typically this
is achieved by implementing the magic method `__invoke` on the controller class. No arguments are passed to the
controller or middleware when they are called.

The interfaces `QL\Panthor\ControllerInterface` and `QL\Panthor\MiddlewareInterface` are provided for convenience that
middleware and controllers may implement, but no type checks are performed.

The Bootstrap and RouteLoader will populate several **runtime services** that are available to be used as dependencies
for any service.

service            | Description
------------------ | -----------
root               | The application root. NO TRAILING SLASH.
slim.environment   | Slim\Environment
slim.request       | Slim\Http\Request
slim.response      | Slim\Http\Response
slim.parameters    | An associative array of route parameters.
slim.halt          | A callable used to abort application execution.

About `slim.halt`:

Usually applications need a way to abort execution of the script. In the case of redirects, errors, or other situations
in which script execution should be terminated early.

This callable requires an http status code. Optionally, you can set the body of the http response. If not provided it
will be empty. Generally this should be avoided in client-facing applications.

Example usage:
```php
call_user_func($halt, 500, 'An unknown error occured');
```

### Application configuration

```
ROOT
├─ bin
│   └─ executables
│
├─ configuration
│   ├─ environment
│   │   ├─ dev.yml
│   │   ├─ test.yml
│   │   ├─ beta.yml
│   │   └─ prod.yml
│   │
│   ├─ bootstrap.php
│   ├─ config.yml
│   ├─ di.yml
│   └─ routes.yml
│
├─ public
│   └─ index.php
│
├─ src
│   └─ ... code
│
└─ testing
    ├─ src
    │   └─ ... testing stubs and mocks
    │
    └─ tests
       └─ ... tests
```

#### configuration/

The configuration directory contains all configuration files, usually in YAML.

Put environment-specific configuration in the respective file under `environment/`. When your application is deployed
to an environment, the matching file is found and merged into the general application configuration.

`bootstrap.php` is the common starting point of your application. It is used by the launch file (index.php), and any
other scripts that need access to the di container or slim application. You may add general application configuration
such as error or session handlers, ini settings, etc.

`config.yml` is used for environment-independent configuration and is the common configuration file that loads
all other configuration. If you would like to break up your config or DI settings into multiple files to maintain your
sanity, just add them to the list of imports at the top of the file.

`di.yml` is where all services should be defined.

`routes.yml` is where all routes should be defined.

#### src/

It's where the code goes.

#### testing/

The testing directory contains two folders:
`src/` and `tests/`.

`tests/` is typically where phpunit tests are located. Nesting the test directory in this manner
potentially allows multiple testing suites while keeping them isolated.

For example, your testing suite may eventually expand to the following:
```
testing
├─ src
│  └─ ... testing stubs and mocks
│
├─ phpunit-tests
│  └─ ... tests
│
├─ phpunit.xml
│
└─ integration-tests
   └─ ... tests
```

Keeping all testing support within this folder allows you to easily exclude it when creating a dist of your application.

### Web server configuration

#### Apache

```
<!-- $SERVER_NAME -->
<!-- $APPLICATION_ROOT -->
<VirtualHost *:80>
    ServerName $SERVER_NAME
    DocumentRoot $APPLICATION_ROOT

    <Directory $APPLICATION_ROOT>
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
    </Directory>
</VirtualHost>
```

#### NGINX

```
tbd
```
