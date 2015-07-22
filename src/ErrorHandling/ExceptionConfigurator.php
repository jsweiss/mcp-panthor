<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use ErrorException;
use Exception;
use QL\HttpProblem\Formatter\JsonFormatter;
use QL\HttpProblem\HttpProblem;
use QL\HttpProblem\HttpProblemException;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\TemplateInterface;
use QL\Panthor\Templating\NullTemplate;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Slim\Http\Response;

/**
 * Extend this class and add additional handlers in the `attach` method to customize how your application handles errors.
 *
 * This configurator assumes you are using "panthor-application", and serves html error pages.
 * If you have a pure API - use APIExceptionConfigurator which ensures all errors are rendered through HttpProblem.
 */
class ExceptionConfigurator
{
    const ERR_INTERNAL = 'Internal Server Error';

    /**
     * Error template
     *
     * @type TemplateInterface
     */
    private $template;

    /**
     * @type ErrorHandler|null
     */
    private $handler;

    /**
     * @param TemplateInterface|null $template
     */
    public function __construct(TemplateInterface $template = null)
    {
        $this->template = $template ?: new NullTemplate;

        $this->handler = null;
    }

    /**
     * @param ErrorHandler $handler
     *
     * @return void
     */
    public function attach(ErrorHandler $handler)
    {
        $this->handler = $handler;

        $this->registerHandlers();
    }

    /**
     * This should be registered last, so other more specific handlers can attempt to handle first.
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function handleBaseException(Exception $exception)
    {
        $this->renderTwigResponse($exception, 500);
    }

    /**
     * @param HttpProblemException $exception
     *
     * @return void
     */
    public function handleHttpProblemException(HttpProblemException $exception)
    {
        $this->renderProblemResponse($exception->problem());
    }

    /**
     * @param NotFoundException $exception
     *
     * @return void
     */
    public function handleNotFoundException(NotFoundException $exception)
    {
        $this->renderTwigResponse($exception, 404);
    }

    /**
     * Fatal errors must "kill" the process and be rendered manually, as the PHP process in an unstable state and has
     * broken out of Slim. We cannot return to the Slim context and render through normal means.
     *
     * @param FatalErrorException $exception
     *
     * @return void
     */
    public function handleSuperFatalException(FatalErrorException $exception)
    {
        $this->renderTwigResponse($exception, 500, true);
    }

    /**
     * Extend this class and override this method to change the handlers for your application.
     *
     * @return void
     */
    protected function registerHandlers()
    {
        $this->register([$this, 'handleNotFoundException']);
        $this->register([$this, 'handleHttpProblemException']);
        $this->register([$this, 'handleSuperFatalException']);
        $this->register([$this, 'handleBaseException']);
    }

    /**
     * @param callable $handler
     *
     * @return void
     */
    protected function register(callable $handler)
    {
        if (!$this->handler) {
            return;
        }

        $this->handler->registerHandler($handler);
    }

    /**
     * Proxy for ErrorHandler::prepareResponse so it can safely fail if never attached correctly.
     *
     * @see ErrorHandler::prepareResponse
     *
     * @return void
     */
    protected function prepareResponse()
    {
        if (!$this->handler) {
            return;
        }

        return call_user_func_array([$this->handler, 'prepareResponse'], func_get_args());
    }

    /**
     * @param Exception $exception
     *
     * @return HttpProblem
     */
    protected function createProblemWithContext(Exception $exception)
    {
        $status = ($exception->getCode() >= 400 && $exception->getCode() < 600) ? (int) $exception->getCode() : 500;
        $title = Response::getMessageForCode($status) ?: self::ERR_INTERNAL;

        $severity = 'Exception';
        if ($exception instanceof ErrorException) {
            $severity = FatalErrorHandler::getErrorType($exception->getSeverity());
        }

        $context = [
            'severity' => $severity,
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ];

        return new HttpProblem($status, $title, $exception->getMessage(), $context);
    }

    /**
     * @param HttpProblem $problem
     * @param bool $kill
     *
     * @return void
     */
    protected function renderProblemResponse(HttpProblem $problem, $kill = false)
    {
        $this->prepareResponse(
            JsonFormatter::content($problem),
            JsonFormatter::status($problem),
            JsonFormatter::headers($problem),
            $kill
        );
    }

    /**
     * Prepare Twig formatted response for output
     *
     * @param Exception $exception
     * @param int $status
     * @param bool $kill
     *
     * @return void
     */
    protected function renderTwigResponse(Exception $exception, $status = 500, $kill = false)
    {
        $context = [
            'message' => $exception->getMessage(),
            'status' => $status,
            'severity' => 'Exception',
            'exception' => $exception
        ];

        if ($exception instanceof ErrorException) {
            $context['severity'] = FatalErrorHandler::getErrorType($exception->getSeverity());
        }

        $rendered = $this->template->render($context);
        $this->prepareResponse($rendered, $status, [], $kill);
    }
}
