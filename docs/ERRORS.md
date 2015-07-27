## Error Handling

### Background

Error handling in PHP sucks. "Errors" exhibit different behavior depending on their type.

- **Exception Handler**

  > Handles exceptions thrown by userland or extensions code. Typically the php core does not throw exceptions.

- **Error Handler**

  > Handles non-super fatals (E_WARN, E_NOTICE, etc). While this catches most errors, not the most serious.

- **Shutdown Handler**

  > The shutdown handler always runs when the PHP process closes, and a handler can be attached to detect whether a "super fatal" occured and can be re-routed to the error handler.

## Super fatals

See [QL\Panthor\ErrorHandling\FatalErrorHandler](../src/ErrorHandling/FatalErrorHandler.php)

This class is very light, and its purpose is to **convert super fatals into exceptions**, and route them back into Slim to be handled by your error handler.

```php
use QL\Panthor\ErrorHandling\FatalErrorHandler;

// Application
$app = $container->get('slim');

# convert errors to exceptions
FatalErrorHandler::register([$app, 'error']);

$app->run();
```

## Error Handler

See [QL\Panthor\ErrorHandling\ErrorHandler](../src/ErrorHandling/ErrorHandler.php)

This handler is responsible for **logging exceptions** and routing them to the Exception dispatcher provided by `ql/exception-toolkit`.

It also acts as a **slim hook**, so it can be attached to slim and errors that occur within the Slim context are routed to the handler.

```php
// Note, this code is for example purposes only, as it can be configured entirely from your DI configuration.

use Exception;
use QL\Panthor\ErrorHandling\ErrorHandler;

$handler = new ErrorHandler($logger);

// Attach to slim, to automatically handle errors.
$handler($slim);

// Exceptions are processed through the handleException method
$handler->handleException(new Exception);
```

## Exception Configurator

- See [QL\Panthor\ErrorHandling\ExceptionConfigurator](../src/ErrorHandling/ExceptionConfigurator.php)
- See [QL\Panthor\ErrorHandling\APIExceptionConfigurator](../src/ErrorHandling/APIExceptionConfigurator.php)

By itself the error handler doesn't actually do anything. It must be configured.
This is where the exception configurators come in. The are registered on the handler and tell the handler what to do.
Different types of errors or exceptions can be handled differently.

**ExceptionConfigurator** handles the following situations:

- `FatalErrorException` - Super fatals, the response must be rendered manually, since the Slim context has died.
- `HttpProblemException` - Http Problem exceptions, for use by APIs.
    - See [Draft RFC http problem](https://tools.ietf.org/html/draft-ietf-appsawg-http-problem-00)
- `NotFoundException` - Not Found exceptions, when the Slim router fails to find a matching route.
- Generic `Exception`

A `TemplateInterface` must be provided to the configurator, as it renders an error page to the client. Except for `HttpProblemException`, which uses a JSON response.

**APIExceptionConfigurator** handles the same exceptions, but converts all to **HttpProblem**, to be rendered out as a JSON response. It also handles the following:

- `RequestException` - Client errors from **RequestBodyMiddleware**.

## Custom Exception Configurators

You can create your own configurator if you wish to customize how errors are handled. It is recommended you extend the original configurator instead, and override this method:

```php
/**
 * Extend this class and override this method to change the handlers for your application.
 *
 * @return void
 */
protected function registerHandlers()
{
    // Default handlers
    $this->register([$this, 'handleNotFoundException']);
    $this->register([$this, 'handleHttpProblemException']);
    $this->register([$this, 'handleSuperFatalException']);
    $this->register([$this, 'handleBaseException']);
}
```

The following example shows how **NotFoundExceptions** can be handled differently in case your application has both an API and html pages.

```php
namespace MyApplication\Slim;

use QL\HttpProblem\HttpProblemException;
use QL\Panthor\ErrorHandling\ExceptionConfigurator as BaseConfigurator;
use QL\Panthor\Exception\NotFoundException;

class ExceptionConfigurator extends BaseConfigurator
{
    /**
     * Replacement of default 404 handler to support api and html responses.
     * {@inheritdoc}
     */
    public function handleNotFoundException(NotFoundException $exception)
    {
        if (isset($_SERVER['REQUEST_URI']) && substr($_SERVER['REQUEST_URI'], 0, 5) === '/api/') {
            return $this->handleHttpProblemException(HttpProblemException::build(404, 'not-found'));
        }

        $this->renderTwigResponse($exception, 404);
    }
}
```

### Example DI Configuration

```yaml
slim.hook.errors:
    class: 'QL\Panthor\ErrorHandling\ErrorHandler'
    arguments: [@logger]
    configurator: [@exception.configurator, 'attach']

exception.configurator:
    class: 'QL\Panthor\ErrorHandling\ExceptionConfigurator'
    arguments: [@error.page.twig]

logger:
    'class': 'Psr\Log\NullLogger'

error.page.twig:
    parent: 'twig.template'
    calls: [['setTemplate', ['error.twig']]]

# If using mcp-logger, attach the mcp logger hook
slim.hook.logger:
    class: 'QL\Panthor\Slim\McpLoggerHook'
    arguments: [@logger.mcp.factory]

```
