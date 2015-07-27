<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use Mockery;
use PHPUnit_Framework_TestCase;
use MCP\DataType\IPv4Address;
use MCP\Logger\Message\MessageFactory;
use Slim\Slim;
use Slim\Http\Request;

class McpLoggerHookTest extends PHPUnit_Framework_TestCase
{
    private $slim;
    private $request;

    public function setUp()
    {
        $this->request = Mockery::mock(Request::CLASS);
        $this->slim = Mockery::mock(Slim::CLASS, ['request' => $this->request]);
        unset($_SERVER['SERVER_ADDR']);
    }

    public function testHookAddsDefaultPropertiesWithInvalidIP()
    {
        $this->request
            ->shouldReceive([
                'getHost' => 'example.com',
                'getReferrer' => 'test.com',
                'getUrl' => 'http://example.com',
                'getPathInfo' => '/page',
                'getUserAgent' => 'test-client',
                'getIp' => 'derp.herp.do',
            ]);

        $setters = [
            'machineName' => 'example.com',
            'referrer' => 'test.com',
            'url' => 'http://example.com/page',
            'userAgentBrowser' => 'test-client',
        ];
        // 'userIPAddress' => '',
        // 'machineIPAddress' => '',

        $factory = Mockery::mock(MessageFactory::CLASS);
        foreach ($setters as $name => $value ) {
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with($name, $value)
            ->once();
        }

        $hook = new McpLoggerHook($factory);
        $hook($this->slim);
    }

    public function testHookAddsDefaultPropertiesWithValidIP()
    {
        $this->request
            ->shouldReceive([
                'getHost' => '',
                'getReferrer' => '',
                'getUrl' => '',
                'getPathInfo' => '',
                'getUserAgent' => '',
                'getIp' => '127.0.0.1',
            ]);

        $_SERVER['SERVER_ADDR'] = '192.168.0.1';

        $factory = Mockery::mock(MessageFactory::CLASS);
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('machineName', Mockery::any());
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('referrer', Mockery::any());
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('url', Mockery::any());
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('userAgentBrowser', Mockery::any());

        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('userIPAddress', Mockery::type(IPv4Address::CLASS))
            ->once();
        $factory
            ->shouldReceive('setDefaultProperty')
            ->with('machineIPAddress', Mockery::type(IPv4Address::CLASS))
            ->once();

        $hook = new McpLoggerHook($factory);
        $hook($this->slim);
    }
}
