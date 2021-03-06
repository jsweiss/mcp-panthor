<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Slim;

use Slim\Slim;
use Slim\Middleware;

/**
 * This middleware restores error handling back to the handler chosen by the app.
 *
 * This is necessary because Slim 2.x resets to its own error handler on Slim:run()
 */
class ProtectErrorHandlerMiddleware extends Middleware
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var int
     */
    private $level;

    /**
     * @param callable $handler
     * @param int $level
     */
    public function __construct(callable $handler, $level = \E_ALL)
    {
        $this->handler = $handler;
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function call()
    {
        set_error_handler($this->handler, $this->level);
        $this->next->call();
    }
}
