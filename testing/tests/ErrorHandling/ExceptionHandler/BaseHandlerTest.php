<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use ErrorException;
use Exception as BaseException;
use Mockery;
use PHPUnit_Framework_TestCase;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\Exception\Exception;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Exception\RequestException;
use QL\Panthor\Testing\MockeryAssistantTrait;
use QL\Panthor\Testing\TestLogger;

class BaseHandlerTest extends PHPUnit_Framework_TestCase
{
    use MockeryAssistantTrait;

    public function testBaseHandlerHandlesEverything()
    {
        $renderer = Mockery::mock(ExceptionRendererInterface::CLASS);

        $handler = new BaseHandler($renderer);

        $handled = $handler->getHandledExceptions();
        $this->assertCount(1, $handled);

        $handled = $handled[0];

        $this->assertInstanceOf($handled, new Exception);
        $this->assertInstanceOf($handled, new NotFoundException);
        $this->assertInstanceOf($handled, new RequestException);
        $this->assertInstanceOf($handled, new BaseException);
    }

    public function testStatusAndContextPassedToRenderer()
    {
        $renderer = Mockery::mock(ExceptionRendererInterface::CLASS);
        $this->spy($renderer, 'render', [500, $this->buildSpy('renderer')]);

        $handler = new BaseHandler($renderer);

        $ex = new Exception('ex msg');
        $this->assertTrue($handler->handle($ex));

        $context = $this->getSpy('renderer');
        $context = $context();

        $this->assertCount(4, $context);

        $this->assertSame('ex msg', $context['message']);
        $this->assertSame(500, $context['status']);
        $this->assertSame('Exception', $context['severity']);
        $this->assertSame($ex, $context['exception']);
    }

    public function testErrorExceptionPassesCorrectSeverityToRenderer()
    {
        $renderer = Mockery::mock(ExceptionRendererInterface::CLASS);
        $this->spy($renderer, 'render', [500, $this->buildSpy('renderer')]);

        $handler = new BaseHandler($renderer);

        $ex = new ErrorException('ex msg', 5, \E_ERROR);
        $this->assertTrue($handler->handle($ex));

        $context = $this->getSpy('renderer');
        $context = $context();

        $this->assertSame('E_ERROR', $context['severity']);
    }

    public function testExceptionIsLogged()
    {
        $logger = new TestLogger;
        $renderer = Mockery::mock(ExceptionRendererInterface::CLASS, ['render' => null]);

        $handler = new BaseHandler($renderer, $logger);

        $ex = new ErrorException('ex msg', 5, \E_ERROR);
        $this->assertTrue($handler->handle($ex));

        $this->assertCount(1, $logger->messages);

        $msg = $logger->messages[0];
        $this->assertSame('error', $msg['level']);
        $this->assertSame('ex msg', $msg['message']);
        $this->assertSame(1, $msg['context']['errorCode']);
        $this->assertSame('E_ERROR', $msg['context']['errorType']);
        $this->assertSame('ErrorException', $msg['context']['errorClass']);

        $this->assertContains('/panthor/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:86', $msg['context']['errorStacktrace']);
    }

    public function testPreviousExceptionIsLoggedInStacktrace()
    {
        $logger = new TestLogger;
        $renderer = Mockery::mock(ExceptionRendererInterface::CLASS, ['render' => null]);

        $handler = new BaseHandler($renderer, $logger);

        $prev = new ErrorException('prev exception', 5, \E_NOTICE);
        $ex = new Exception('ex msg', 5, $prev);
        $this->assertTrue($handler->handle($ex));

        $this->assertCount(1, $logger->messages);

        $msg = $logger->messages[0];
        $this->assertSame('error', $msg['level']);
        $this->assertSame('ex msg', $msg['message']);
        $this->assertSame(0, $msg['context']['errorCode']);
        $this->assertSame('QL\Panthor\Exception\Exception', $msg['context']['errorType']);
        $this->assertSame('QL\Panthor\Exception\Exception', $msg['context']['errorClass']);

        $this->assertContains('/panthor/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:108', $msg['context']['errorStacktrace']);
        $this->assertContains('prev exception', $msg['context']['errorStacktrace']);

        $this->assertContains('/panthor/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:109', $msg['context']['errorStacktrace']);
        $this->assertContains('ex msg', $msg['context']['errorStacktrace']);
    }
}
