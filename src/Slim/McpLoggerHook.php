<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Slim;

use MCP\DataType\IPv4Address;
use MCP\Logger\MessageFactoryInterface;
use Slim\Slim;

/**
 * This hook requires "ql/mcp-logger".
 *
 * Set default log message properties.
 *
 * This hook should be attached to the "slim.before" event.
 */
class McpLoggerHook
{
    /**
     * @type MessageFactoryInterface
     */
    private $factory;

    /**
     * @param MessageFactoryInterface $factory
     */
    public function __construct(MessageFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Slim $slim
     *
     * @return void
     */
    public function __invoke(Slim $slim)
    {
        $request = $slim->request();

        // server
        $this->factory->setDefaultProperty('machineName', $request->getHost());

        // this property is broken, core logger ignores it
        // $this->factory->setDefaultProperty('requestMethod',  $request->getMethod());

        // client
        $this->factory->setDefaultProperty('referrer',  $request->getReferrer());
        $this->factory->setDefaultProperty('url',  $request->getUrl() . $request->getPathInfo());
        $this->factory->setDefaultProperty('userAgentBrowser', $request->getUserAgent());

        if ($userIP = IPv4Address::create($request->getIp())) {
            $this->factory->setDefaultProperty('userIPAddress', $userIP);
        }

        // slim doesn't expose this var
        if (!isset($_SERVER['SERVER_ADDR'])) {
            return;
        }

        if ($serverIP = IPv4Address::create($_SERVER['SERVER_ADDR'])) {
            $this->factory->setDefaultProperty('machineIPAddress', $serverIP);
        }
    }
}
