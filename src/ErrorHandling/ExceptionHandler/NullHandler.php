<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
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
