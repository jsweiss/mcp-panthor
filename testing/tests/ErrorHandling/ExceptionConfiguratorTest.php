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
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Templating\NullTemplate;
use Symfony\Component\Debug\Exception\FatalErrorException;

class ExceptionConfiguratorTest extends PHPUnit_Framework_TestCase
{
    public function testHandlingBaseException()
    {
        $ex = new Exception('testmessage', 5);

        $template = Mockery::mock(NullTemplate::CLASS);
        $template
            ->shouldReceive('render')
            ->with([
                'message' => 'testmessage',
                'status' => 500,
                'severity' => 'Exception',
                'exception' => $ex
            ])
            ->andReturn('rendered-output')
            ->once();

        $configurator = new ExceptionConfigurator($template);
        $configurator->handleBaseException($ex);
    }

    public function testHandlingNotFoundException()
    {
        $ex = new NotFoundException;

        $template = Mockery::mock(NullTemplate::CLASS);
        $template
            ->shouldReceive('render')
            ->with([
                'message' => '',
                'status' => 404,
                'severity' => 'Exception',
                'exception' => $ex
            ])
            ->once();

        $configurator = new ExceptionConfigurator($template);
        $configurator->handleNotFoundException($ex);
    }

    public function testHandlingSuperFatalException()
    {
        $ex = new FatalErrorException('error', 5, \E_ERROR, 'script.php', 50);

        $handler = Mockery::mock(ErrorHandler::CLASS, ['registerHandler' => null]);
        $handler
            ->shouldReceive('prepareResponse')
            ->with('output', 500, [], true)
            ->once();

        $template = Mockery::mock(NullTemplate::CLASS);
        $template
            ->shouldReceive('render')
            ->with([
                'message' => 'error',
                'status' => 500,
                'severity' => 'Error',
                'exception' => $ex
            ])
            ->andReturn('output')
            ->once();

        $configurator = new ExceptionConfigurator($template);
        $configurator->attach($handler);
        $configurator->handleSuperFatalException($ex);
    }

    public function testHandlingHttpProblemException()
    {
        $ex = HttpProblemException::build(403, 'test', 'more information');

        $expectedJSON = '{"status":403,"title":"test","type":"about:blank","detail":"more information"}';

        $handler = Mockery::mock(ErrorHandler::CLASS, ['registerHandler' => null]);
        $handler
            ->shouldReceive('prepareResponse')
            ->with($expectedJSON, 403, ['Content-Type' => 'application/problem+json'], false)
            ->once();

        $configurator = new ExceptionConfigurator();
        $configurator->attach($handler);
        $configurator->handleHttpProblemException($ex);
    }

    public function testConfiguratorPreparesResponseIfAttached()
    {
        $ex = new Exception('testmessage', 5);

        $handler = Mockery::mock(ErrorHandler::CLASS, ['registerHandler' => null]);
        $handler
            ->shouldReceive('prepareResponse')
            ->with('rendered-output', 500, [], false)
            ->once();

        $template = Mockery::mock(NullTemplate::CLASS);
        $template
            ->shouldReceive('render')
            ->with([
                'message' => 'testmessage',
                'status' => 500,
                'severity' => 'Exception',
                'exception' => $ex
            ])
            ->andReturn('rendered-output')
            ->once();

        $configurator = new ExceptionConfigurator($template);
        $configurator->attach($handler);
        $configurator->handleBaseException($ex);
    }

    public function testHandlersAreRegistered()
    {
        $handler = Mockery::mock(ErrorHandler::CLASS);

        $configurator = new ExceptionConfigurator;

        $handler
            ->shouldReceive('registerHandler')
            ->with([$configurator, 'handleNotFoundException'])
            ->once();
        $handler
            ->shouldReceive('registerHandler')
            ->with([$configurator, 'handleHttpProblemException'])
            ->once();
        $handler
            ->shouldReceive('registerHandler')
            ->with([$configurator, 'handleSuperFatalException'])
            ->once();
        $handler
            ->shouldReceive('registerHandler')
            ->with([$configurator, 'handleBaseException'])
            ->once();

        $configurator->attach($handler);
    }
}
