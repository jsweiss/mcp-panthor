### Application structure and configuration

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
    ├─ fixtures
    │   └─ ... testing fixtures
    │
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
├─ fixtures
│   └─ ... testing fixtures
│
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

