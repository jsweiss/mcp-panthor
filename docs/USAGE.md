## Usage

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

Service            | Description
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
