<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use QL\Panthor\Exception\HTTPProblemException;
use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;

class HTTPProblemHandler implements ExceptionHandlerInterface
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
            HTTPProblemException::CLASS
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

        $status = $throwable->problem()->status();

        $context = [
            'message' => $throwable->getMessage(),
            'status' => $status,
            'severity' => 'Problem',
            'exception' => $throwable
        ];

        $this->renderer->render($status, $context);

        return true;
    }
}
