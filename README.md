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
