<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use ErrorException;
use Exception as BaseException;
use PHPUnit_Framework_TestCase;
use QL\Panthor\Exception\Exception;

class NullHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testNullHandlerNeverResponds()
    {
        $handler = new NullHandler;

        $this->assertFalse($handler->handle(new BaseException));
        $this->assertFalse($handler->handle(new ErrorException));

        $this->assertFalse($handler->handle(new Exception));
    }

    public function testNullHandlerCantEven()
    {
        $handler = new NullHandler;

        $this->assertCount(0, $handler->getHandledExceptions());
    }
}
