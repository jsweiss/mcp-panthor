<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use Exception;
use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\Exception\NotFoundException;

/**
 * Handler for 404s
 */
class NotFoundHandler implements ExceptionHandlerInterface
{
    /**
     * @type ExceptionRendererInterface
     */
    private $renderer;

    /**
     * @param ExceptionRendererInterface $renderer
     */
    public function __construct(ExceptionRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledExceptions()
    {
        return [NotFoundException::CLASS];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $exception)
    {
        if (!$exception instanceof NotFoundException) return false;

        $status = 404;
        $context = [
            'message' => 'Page Not Found',
            'status' => $status,
            'severity' => 'NotFound',
            'exception' => $exception
        ];

        $this->renderer->render($status, $context);

        return true;
    }
}
