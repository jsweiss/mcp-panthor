<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use Slim\Slim;

/**
 * Apache does not pass the Authorization header to PHP by default.
 *
 * This hook will populate the authorization header for apache deployments.
 *
 * It should be attached to the "slim.before" event.
 */
class ApacheAuthorizationHeaderHook
{
    /**
     * @type callable|string
     */
    private $getHeaderFunction;

    /**
     * @param callable|string $getHeaderFunction
     */
    public function __construct($getHeaderFunction = 'apache_request_headers')
    {
        $this->getHeaderFunction = $getHeaderFunction;
    }

    /**
     * @param Slim $slim
     * @return null
     */
    public function __invoke(Slim $slim)
    {
        $headers = $slim->request()->headers;

        if ($headers->has('Authorization')) {
            return;
        }

        if (is_callable($this->getHeaderFunction)) {
            $customHeaders = call_user_func($this->getHeaderFunction);
            if (is_array($customHeaders) && array_key_exists('Authorization', $customHeaders)) {
                $headers->set('Authorization', $customHeaders['Authorization']);
            }
        }
    }
}
