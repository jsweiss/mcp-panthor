<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\Exception\NotFoundException;

/**
 * Handler for 404s
 */
class NotFoundHandler implements ExceptionHandlerInterface
{
    use HandledExceptionsTrait;

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

        $this->setHandledThrowables([
            NotFoundException::CLASS
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function handle($throwable)
    {
        if (!$this->canHandleThrowable($throwable)) {
            return false;
        }

        $status = 404;
        $context = [
            'message' => 'Page Not Found',
            'status' => $status,
            'severity' => 'NotFound',
            'exception' => $throwable
        ];

        $this->renderer->render($status, $context);

        return true;
    }
}
