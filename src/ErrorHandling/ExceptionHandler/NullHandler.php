<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;

/**
 * Null handler, it never handles exceptions.
 */
class NullHandler implements ExceptionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($throwable)
    {
        return false;
    }
}
