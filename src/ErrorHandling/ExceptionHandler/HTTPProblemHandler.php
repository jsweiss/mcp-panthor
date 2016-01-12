<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\ErrorHandling\ExceptionHandler;

use Exception;
use QL\Panthor\Exception\HTTPProblemException;
use QL\Panthor\ErrorHandling\ExceptionHandlerInterface;
use QL\Panthor\ErrorHandling\ExceptionRendererInterface;

class HTTPProblemHandler implements ExceptionHandlerInterface
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
        return [HTTPProblemException::CLASS];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Exception $exception)
    {
        if (!$exception instanceof HTTPProblemException) return false;

        $status = $exception->problem()->status();

        $context = [
            'message' => $exception->getMessage(),
            'status' => $status,
            'severity' => 'Problem',
            'exception' => $exception
        ];

        $this->renderer->render($status, $context);

        return true;
    }
}
