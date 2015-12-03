<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;

interface ExceptionHandlerInterface
{
    /**
     * Return a list of full qualified class names of exceptions this handler can handle.
     *
     * @return string[]
     */
    public function getHandledExceptions();

    /**
     * Handle an exception, and return whether the exception was handled and the remaining stack should be aborted.
     *
     * @param Exception $exception
     *
     * @return bool
     */
    public function handle(Exception $exception);
}
