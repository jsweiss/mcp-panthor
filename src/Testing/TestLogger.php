<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Testing;

use Psr\Log\AbstractLogger;

/**
 * A logger that records messages into an easy to inspect public property.
 *
 * Usage:
 *
 * $logger = new TestLogger;
 *
 * $logger->info('message');
 * $logger->emergency('message 2', ['data' => 'testing']);
 *
 * var_dump($logger->messages);
 * [
 *     [
 *         'level' => 'info',
 *         'message' => 'message',
 *         'context' => []
 *     ],
 *     [
 *         'level' => 'emergency',
 *         'message' => 'message 2',
 *         'context' => ['data' => 'testing']
 *     ]
 * ]
 *
 */
class TestLogger extends AbstractLogger
{
    /**
     * @type array
     */
    public $messages;

    public function __construct()
    {
        $this->messages = [];
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = [])
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
    }
}
