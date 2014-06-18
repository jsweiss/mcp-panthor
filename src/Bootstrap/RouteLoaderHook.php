<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Bootstrap;

use RuntimeException;
use Slim\Slim;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Convert a flat array into slim routes and attaches them to the slim application.
 *
 * This hook should be attached to the "slim.before.router" event.
 *
 * Note:
 * This hook will inject several framework services into the container. They MUST
 * be marked as synthetic in the container configuration.
 *
 * - slim.environment   : Slim\Environment
 * - slim.request       : Slim\Http\Response
 * - slim.response      : Slim\Http\Response
 * - slim.parameters    : Named array of route parameters
 * - slim.halt          : Callable that aborts execution of the application.
 *     Requires an http status code. Optionally the response body may be
 *     provided as the second parameter.
 */
class RouteLoaderHook
{
    /**
     * A hash of valid http methods. The keys are the methods.
     *
     * @type array
     */
    private $methods;

    /**
     * @type ContainerInterface
     */
    private $container;

    /**
     * @type array
     */
    private $routes;

    /**
     * @param ContainerInterface $container
     * @param array $routes
     */
    public function __construct(ContainerInterface $container, array $routes)
    {
        $this->container = $container;
        $this->routes = $routes;

        // These are the only methods supported by Slim
        $validMethods = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT'];
        $this->methods = array_fill_keys($validMethods, true);
    }

    /**
     * Load routes into the application
     *
     * @param Slim $slim
     *
     * @return null
     */
    public function __invoke(Slim $slim)
    {
        foreach ($this->routes as $name => $details) {

            $methods = $this->methods($details);
            $conditions = $this->nullable('conditions', $details);
            $url = $details['route'];
            $stack = $this->convertStackToCallables($details['stack']);


            // Prepend the runtime service injector to the stack
            // This will ensure all middleware and the controller have access to slim services
            array_unshift($stack, $this->runtimeServicesInjector($slim));

            // Prepend the url to the stack
            array_unshift($stack, $url);

            // Create route
            // Special note: slim is really stupid in the way it uses func_get_args EVERYWHERE
            $route = call_user_func_array([$slim, 'map'], $stack);
            call_user_func_array([$route, 'via'], $methods);

            // Add Name
            $route->name($name);

            // Add Conditions
            if ($conditions) {
                $route->conditions($conditions);
            }
        }
    }

    /**
     * Get a callback that will inject Slim services into the symfony service container.
     *
     * This method may be overridden by applications to inject custom services.
     *
     * @param Slim $slim
     *
     * @return Closure
     */
    protected function runtimeServicesInjector(Slim $slim)
    {
        return function() use ($slim) {
            $this->container->set('slim.environment', $slim->environment());

            $this->container->set('slim.request', $slim->request());
            $this->container->set('slim.response', $slim->response());

            $this->container->set('slim.parameters', $slim->router()->getCurrentRoute()->getParams());
            $this->container->set('slim.halt', [$slim, 'halt']);
        };
    }

    /**
     * Convert an array of keys to middleware callables
     *
     * @param string[] $stack
     *
     * @return callable[]
     */
    private function convertStackToCallables(array $stack)
    {
        foreach ($stack as &$key) {
            $key = function () use ($key) {
                call_user_func($this->container->get($key));
            };
        }

        return $stack;
    }

    /**
     * @param array $routeDetails
     * @throws RuntimeException
     *
     * @return string[]
     */
    private function methods(array $routeDetails)
    {
        // No method matches ANY method
        if (!$methods = $this->nullable('method', $routeDetails)) {
            return ['ANY'];
        }

        if ($methods && !is_array($methods)) {
            $methods = [$methods];
        }

        // check for invalid method types
        foreach ($methods as $method) {
            if (!isset($this->methods[$method])) {
                throw new RuntimeException(sprintf('Unknown HTTP method: %s', $method));
            }
        }

        if ($methods === ['GET']) {
            array_push($methods, 'HEAD');
        }

        return $methods;
    }

    /**
     * @param string $key
     * @param array $data
     *
     * @return mixed
     */
    private function nullable($key, array $data)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }
}
