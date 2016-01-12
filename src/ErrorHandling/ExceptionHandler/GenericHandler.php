<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use Exception;
use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;

/**
 * Generic Handler for exceptions. Useful if you want to build a closure or some arbitrary callable to handle exceptions.
 *
 * The callable handler should have the following signature:
 *
 * ```
 * function(Exception $exception) : bool {
 *   // logic
 * }
 * ```
 */
class GenericHandler implements ExceptionHandlerInterface
{
    /**
     * @type callable
     */
    private $handler;

    /**
     * @type string[]
     */
    private $supportedTypes;

    /**
     * @param string[] $supportedTypes
     * @param callable $handler
     */
    public function __construct(array $supportedTypes, callable $handler)
    {
        $this->supportedTypes = $supportedTypes;
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledExceptions()
    {
        return $this->supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $exception)
    {
        return call_user_func($this->handler, $exception);
    }
}
