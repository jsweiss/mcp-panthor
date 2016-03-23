<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;
use QL\Panthor\Exception\RequestException;

/**
 * Handler for 400s and other client requests.
 */
class RequestExceptionHandler implements ExceptionHandlerInterface
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
            RequestException::CLASS
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

        $status = $throwable->getCode();
        if ($status < 400 || $status >= 500) {
            $status = 400;
        }

        $context = [
            'message' => $throwable->getMessage(),
            'status' => $status,
            'severity' => 'Exception',
            'exception' => $throwable
        ];

        $this->renderer->render($status, $context);

        return true;
    }
}
