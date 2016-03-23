<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
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
}
