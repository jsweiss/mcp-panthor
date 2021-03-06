### Application Structure and Configuration

- [Back to Documentation](README.md)
- Application Structure
- [How To Use](USAGE.md)
- [Error Handling](ERRORS.md)
- [Web Server Configuration](SERVER.md)

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

`bootstrap.php`
> The common starting point of your application. It is used by the launch file (index.php), and any other scripts that
> need access to the di container or slim application. You may add general application configuration such as error or
> session handlers, ini settings, etc.
>
> Example:
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
> // Set Timezone to UTC
> ini_set('date.timezone', 'UTC');
> date_default_timezone_set('UTC');
>
> // Set multibyte encoding
> mb_internal_encoding('UTF-8');
> mb_regex_encoding('UTF-8');
>
> $container = Di::getDi($root, CachedContainer::class);
>
> return $container;
> ```

`config.yml`
> It is used for environment-independent configuration and is the common configuration file that loads all other
> configuration. If you would like to break up your config or DI settings into multiple files to maintain your
> sanity, just add them to the list of imports at the top of the file.
>
> Example:
> ```yaml
> imports:
>     - resource: ../vendor/ql/mcp-panthor/configuration/panthor.yml
>     - resource: di.yml
>     - resource: routes.yml
>
> parameters:
>     cookie.encryption.secret: '' # 128-character hexademical string. Used for cookie encryption with libsodium.
>
>     slim.hooks:
>         slim.before:
>             - 'testapplication.custom.hook'
>         slim.before.router:
>             - 'slim.hook.routes'
> ```

`di.yml`
> Where all DI service definitions should be defined.
>
> Example:
> ```yaml
> services:
>     page.hello_world:
>         class: 'TestApplication\TestController'
>         arguments:
>             - '@slim.response'
> ```

`routes.yml`
> Where all routes should be defined.
>
> Example:
> ```yaml
> parameters:
>     routes:
>         # Basic route
>         # No method will match ANY method
>         hello_world:
>             route: '/'
>             stack: ['page.hello_world']
>
>         # Route with parameter and conditions
>         # example.routeParameter:
>         #     method: 'GET'
>         #     route: '/example/entities/:id'
>         #     stack: ['page.example']
>         #     conditions:
>         #         id: '[\d]+'
>
>         # Route that matches multiple methods
>         # example.multipleMethods:
>         #     method:
>         #         - 'GET'
>         #         - 'POST'
>         #         - 'PUT'
>         #     route: '/example/new'
>         #     stack: ['page.example']
>
>         # Route with middleware. The controller is always the last service.
>         # example.middleware:
>         #     method: 'GET'
>         #     route: '/example/remove'
>         #     stack:
>         #         - 'middleware.request_body'
>         #         - 'page.example'
> ```

#### src/

It's where the code goes.

Example controller:
> ```php
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

