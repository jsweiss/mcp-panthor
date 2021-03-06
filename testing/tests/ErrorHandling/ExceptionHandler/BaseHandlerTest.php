<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use ErrorException;
use Exception as BaseException;
use Mockery;
use PHPUnit_Framework_TestCase;
use QL\MCP\Common\Testing\MemoryLogger;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\Exception\Exception;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Exception\RequestException;
use QL\Panthor\Testing\MockeryAssistantTrait;

class BaseHandlerTest extends PHPUnit_Framework_TestCase
{
    use MockeryAssistantTrait;

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
        $logger = new MemoryLogger;
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

        $this->assertContains('/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:69', $msg['context']['errorStacktrace']);
    }

    public function testPreviousExceptionIsLoggedInStacktrace()
    {
        $logger = new MemoryLogger;
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

        $this->assertContains('/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:91', $msg['context']['errorStacktrace']);
        $this->assertContains('prev exception', $msg['context']['errorStacktrace']);

        $this->assertContains('/testing/tests/ErrorHandling/ExceptionHandler/BaseHandlerTest.php:92', $msg['context']['errorStacktrace']);
        $this->assertContains('ex msg', $msg['context']['errorStacktrace']);
    }
}
