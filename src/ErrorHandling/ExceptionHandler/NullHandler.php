<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use Exception;
use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;

/**
 * Null handler, it never handles exceptions.
 */
class NullHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHandledExceptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $exception)
    {
        return false;
    }
}
