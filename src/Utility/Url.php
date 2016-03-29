<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Utility;

// use Slim\Http\Request;
// use Slim\Http\Response;
// use Slim\Exception\Stop;
use Slim\Router;

class Url
{
    /**
     * @type Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Get the name of the current route.
     *
     * @return string|null
     */
    // public function currentRoute()
    // {
    //     if (!$route = $this->router->getCurrentRoute()) {
    //         return null;
    //     }

    //     return $route->getName();
    // }

    /**
     * Get the relative URL for a given route name.
     *
     * @param string $route
     * @param array $params
     * @param array $query
     *
     * @return string
     */
    public function urlFor($route, array $params = [], array $query = [])
    {
        if (!$route) {
            return '';
        }

        $urlPath = $this->router->relativePathFor($route, $params, $query);
    }

    /**
     * Get the absolute URL for a given route name.
     *
     * @param string $route
     * @param array $params
     * @param array $query
     *
     * @return string
     */
    // public function absoluteUrlFor($route, array $params = [], array $query = [])
    // {
    //     return $this->request->getUrl() . $this->urlFor($route, $params, $query);
    // }

    /**
     * Generate a redirect response for a given route name and halt the application.
     *
     * @param string $route
     * @param array $params
     * @param array $query
     * @param int $code
     *
     * @throws Stop
     */
    // public function redirectFor($route, array $params = [], array $query = [], $code = 302)
    // {
    //     $url = $this->absoluteUrlFor($route, $params);
    //     $this->redirectForURL($url, $query, $code);
    // }

    /**
     * Generate a redirect response for a given URL and halt the application.
     *
     * @param string $url
     * @param array $query
     * @param int $code
     *
     * @throws Stop
     */
    // public function redirectForURL($url, array $query = [], $code = 302)
    // {
    //     $this->response->headers->set('Location', $this->appendQueryString($url, $query));

    //     call_user_func($this->halt, $code);
    // }

    /**
     * @param string $url
     * @param array $query
     *
     * @return string
     */
    // private function appendQueryString($url, array $query)
    // {
    //     if (count($query)) {
    //         $url = sprintf('%s?%s', $url, http_build_query($query));
    //     }

    //     return $url;
    // }
}
