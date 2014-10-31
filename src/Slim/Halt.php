<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use Slim\Slim;

/**
 * Simple proxy for Slim::halt
 */
class Halt
{
    /**
     * @type Slim
     */
    private $slim;

    /**
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    /**
     * @see Slim::halt
     *
     * @param int $status
     * @param string $message
     */
    public function __invoke($status, $message = '')
    {
        return call_user_func_array([$this->slim, 'halt'], func_get_args());
    }
}
