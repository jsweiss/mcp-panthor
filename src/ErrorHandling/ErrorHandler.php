<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use QL\ExceptionToolkit\ExceptionDispatcher;
use QL\HttpProblem\HttpProblemException;
use QL\Panthor\Exception\NotFoundException;
use QL\Panthor\Exception\RequestException;
use Slim\Http\Response;
use Slim\Slim;

/**
 * This handler requires:
 * - "ql/exception-toolkit"
 * - "psr/log"
 *
 * Also recommended:
 * - "ql/http-problem"
 * - "symfony/debug"
 *
 * It should be should be attached to the "slim.before" event.
 *
 * This class is responsible for:
 * - Registering error handler on slim
 * - Logging errors/exceptions
 * - Routing exceptions to the dispatcher
 * - Forcing the response to render in the case of super fatals
 */
class ErrorHandler
{
    const LOG_LEVEL = 'error';

    // ::forceSendResponse()
    use SlimRenderingTrait;

    // ::formatStacktrace()
    // ::setStacktraceLogging()
    use StacktraceFormatterTrait;

    /**
     * @type ExceptionDispatcher
     */
    private $dispatcher;

    /**
     * @type LoggerInterface|null
     */
    private $logger;

    /**
     * @type callable
     */
    private $headerSetter;

    /**
     * The slim instance the hook has been attached to.
     *
     * @type Slim|null
     */
    private $slim;

    /**
     * @param ExceptionDispatcher $dispatcher
     * @param LoggerInterface|null $logger
     * @param callable|null $headerSetter
     */
    public function __construct(
        LoggerInterface $logger = null,
        ExceptionDispatcher $dispatcher = null,
        callable $headerSetter = null
    ) {
        $this->logger = $logger ?: new NullLogger;
        $this->dispatcher = $dispatcher ?: new ExceptionDispatcher;
        $this->headerSetter = $headerSetter ?: $this->getDefaultHeaderSetter();

        $this->slim = null;
    }

    /**
     * @param Slim $slim
     *
     * @return void
     */
    public function __invoke(Slim $slim)
    {
        $this->slim = $slim;

        // Register Global Exception Handler
        $slim->notFound([$this, 'handleNotFound']);

        // Register Global Exception Handler
        $slim->error([$this, 'handleException']);
    }

    /**
     * @param Exception $exception
     *
     * @return void
     */
    public function handleException(Exception $exception)
    {
        if ($this->shouldLogException($exception)) {
            $this->log($exception);
        }

        $this->dispatcher->dispatch($exception);
        if ($this->slim) $this->slim->stop();
    }

    /**
     * @throws NotFoundException
     *
     * @return void
     */
    public function handleNotFound()
    {
        throw new NotFoundException('Not Found', 404);
    }

    /**
     * Prepare response for output
     *
     * @param string $body
     * @param int $status
     * @param array $headers
     * @param bool $kill
     *                Pass true to end the response and force the response to output. Only needed for superfatals.
     *
     * @return void
     */
    public function prepareResponse($body, $status = 500, $headers = [], $kill = false)
    {
        if (!$this->slim || !$this->slim->response()) {
            // Silently fail if never invoked
            return;
        }

        $response = $this->slim->response();

        $response->setBody($body);
        $response->setStatus($status);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        if ($kill) {
            $this->forceSendResponse($this->slim);
        }
    }

    /**
     * @param callable $callable
     *
     * @return void
     */
    public function registerHandler(callable $callable)
    {
        $this->dispatcher->add($callable);
    }

    /**
     * @param array $handlers
     *
     * @return void
     */
    public function registerHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    /**
     * Override this method to customize your logic for determining whether an exception should be logged.
     */
    protected function shouldLogException(Exception $exception)
    {
        $status = $exception->getCode();

        if ($exception instanceof NotFoundException) {
            return false;
        }

        if ($exception instanceof RequestException) {
            return false;
        }

        if ($exception instanceof HttpProblemException && $status < 500) {
            return false;
        }

        return true;
    }

    /**
     * Create and send a log message.
     *
     * Unfortunately, Slim treats ALL warnings, errors, and fatals the same. So logging different levels is not important.
     * All exceptions are sent to this method, so it must filter exceptions we think may not actually be errors (e.g. HttpProblem)
     *
     * @param Exception $exception
     *
     * @return void
     */
    private function log(Exception $exception)
    {
        $level = static::LOG_LEVEL;

        $context = [
            'exceptionClass' => get_class($exception),
            'exceptionData' => $this->formatStacktrace($exception),
        ];

        if ($previous = $exception->getPrevious()) {
            $context['previousExceptionClass'] = get_class($previous);
            $context['previousExceptionData'] = $this->formatStacktrace($previous);
        }

        call_user_func([$this->logger, $level], $exception->getMessage(), $context);
    }

    /**
     * @return callable
     */
    private function getDefaultHeaderSetter()
    {
        return 'header';
    }
}
