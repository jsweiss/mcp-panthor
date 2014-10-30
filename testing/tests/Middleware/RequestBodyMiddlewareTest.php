<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Middleware;

use Mockery;
use PHPUnit_Framework_TestCase;

class RequestBodyMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->di = Mockery::mock('Symfony\Component\DependencyInjection\Container');
        $this->request = Mockery::mock('Slim\Http\Request');
        $this->json = Mockery::mock('QL\Panthor\Utility\Json');
    }

    /**
     * @expectedException QL\Panthor\Exception\RequestException
     */
    public function testUnsupportedType()
    {
        $this->request
            ->shouldReceive(['getMediaType' => 'text/plain']);

        $mw = new RequestBodyMiddleware($this->di, $this->request, $this->json, 'service.name');
        $mw();
    }

    public function testEmptyPostMeansPartyTime()
    {
        $this->request
            ->shouldReceive([
                'getMediaType' => 'application/json',
                'getBody' => '{}',
            ]);

        $this->json
            ->shouldReceive('__invoke')
            ->with('{}')
            ->andReturn([]);

        $this->di
            ->shouldReceive('set')
            ->with('service.name', [RequestBodyMiddleware::NOFUNZONE])
            ->andReturn([]);

        $mw = new RequestBodyMiddleware($this->di, $this->request, $this->json, 'service.name');
        $mw();
    }

    public function testEmptyJsonWithDefaultKeys()
    {
        $this->request
            ->shouldReceive([
                'getMediaType' => 'application/json',
                'getBody' => '{}',
            ]);

        $this->json
            ->shouldReceive('__invoke')
            ->with('{}')
            ->andReturn([]);

        $this->di
            ->shouldReceive('set')
            ->with('service.name', ['key1' => null, 'key2' => null])
            ->andReturn([]);

        $mw = new RequestBodyMiddleware($this->di, $this->request, $this->json, 'service.name');
        $mw->setDefaultKeys(['key1', 'key2']);
        $mw();
    }
}
