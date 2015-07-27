<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;
use Mockery;
use PHPUnit_Framework_TestCase;
use QL\HttpProblem\HttpProblemException;
use QL\Panthor\Testing\TestLogger;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Exception\RequestException;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Slim;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
    private $slim;
    private $response;
    private $dispatcher;

    public function setUp()
    {
        $this->response = Mockery::mock(Response::CLASS);
        $this->slim = Mockery::mock(Slim::CLASS, ['response' => $this->response]);
        $this->dispatcher = Mockery::mock('QL\ExceptionToolkit\ExceptionDispatcher');
    }

    public function testHandlerIsAttached()
    {
        $handler = new ErrorHandler;

        $this->slim
            ->shouldReceive('notFound')
            ->with([$handler, 'handleNotFound'])
            ->once();

        $this->slim
            ->shouldReceive('error')
            ->with([$handler, 'handleException'])
            ->once();

        $handler($this->slim);
    }

    public function testNonErrorExceptionsAreNotLogged()
    {
        $logger = new TestLogger;
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->times(5);

        $handler = new ErrorHandler($logger, $this->dispatcher);

        $handler->handleException(new Exception);
        $handler->handleException(new NotFoundException);
        $handler->handleException(new RequestException);
        $handler->handleException(HttpProblemException::build(500, 'test'));
        $handler->handleException(HttpProblemException::build(403, 'test'));

        $this->assertCount(2, $logger->messages);
    }

    /**
     * @expectedException QL\Panthor\Exception\NotFoundException
     */
    public function testNotFoundExceptionIsThrownWhenNotFoundEventTriggered()
    {
        $handler = new ErrorHandler;

        $handler->handleNotFound();
    }

    public function testNonAttachedHandlerFailsGracefully()
    {
        $handler = new ErrorHandler;
        $actual = $handler->prepareResponse('test');
        $this->assertSame(null, $actual);
    }

    public function testPrepareResponseOnAttachedHandler()
    {
        $this->slim
            ->shouldReceive('notFound');
        $this->slim
            ->shouldReceive('error');

        $this->response
            ->shouldReceive('setBody')
            ->with('test')
            ->once();
        $this->response
            ->shouldReceive('setStatus')
            ->with(500)
            ->once();

        $this->response->headers = Mockery::mock(Headers::CLASS);
        $this->response->headers
            ->shouldReceive('set')
            ->with('testheader', 'abc')
            ->once();

        $handler = new ErrorHandler;
        $handler($this->slim);

        $handler->prepareResponse('test', 500, ['testheader' => 'abc']);
    }
}
