<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use Exception as BaseException;
use PHPUnit_Framework_TestCase;
use QL\Panthor\Exception\Exception;
use QL\Panthor\Exception\RequestException;

class GenericHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testHandlesNothingIfNoSupportedTypes()
    {
        $handler = new GenericHandler([], function() {});

        $this->assertCount(0, $handler->getHandledExceptions());

        $this->assertNull($handler->handle(new Exception));
        $this->assertNull($handler->handle(new RequestException));
        $this->assertNull($handler->handle(new BaseException));
    }

    public function testHandlerCalledIfSupported()
    {
        $exceptions = [];
        $closure = function($ex) use (&$exceptions) {
            $exceptions[] = $ex;
            return true;
        };

        $handler = new GenericHandler([Exception::CLASS, RequestException::CLASS], $closure);

        $this->assertTrue($handler->handle(new Exception));
        $this->assertTrue($handler->handle(new RequestException));

        $this->assertCount(2, $exceptions);
    }
}
