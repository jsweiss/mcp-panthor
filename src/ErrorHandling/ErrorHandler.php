<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;
use Psr\Log\LoggerInterface;
use QL\ExceptionToolkit\ExceptionDispatcher;
use QL\HttpProblem\HttpProblemException;
use QL\Panthor\Exception\NotFoundException;
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
     * @type string
     */
    private $root;

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
        $this->logger = $logger;
        $this->dispatcher = $dispatcher ?: new ExceptionDispatcher;

        if (!$headerSetter) {
            $headerSetter = $this->getDefaultHeaderSetter();
        }

        $this->headerSetter = $headerSetter;
        $this->slim = null;
        $this->root = $this->findAppRoot();
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

        if ($exception instanceof HttpProblemException && $status < 500) {
            return false;
        }

        return true;
    }

    /**
     * Force sending of the response and end the php process.
     *
     * This is copypasta from Slim\Slim::run, as once an error occurs and the application has broken out of Slim's
     * handling context, Slim cannot be made to re-render the response.
     *
     * @param Slim $slim
     *
     * @return void
     */
    private function forceSendResponse(Slim $slim)
    {
        list($status, $headers, $body) = $slim->response()->finalize();

        $header = $this->headerSetter;

        if (headers_sent() === false) {

            //Send status
            $header(sprintf('HTTP/%s %s', $slim->config('http.version'), Response::getMessageForCode($status)));

            // send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    $header(sprintf('%s: %s', $name, $hVal), false);
                }
            }
        }

        // do not set body for HEAD requests
        if ($slim->request->isHead()) {
            return;
        }

        echo $body;
        exit();
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
     * @param Exception $exception
     *
     * @return string
     */
    private function formatStacktrace(Exception $exception)
    {
        $trace = $this->formatStacktraceEntry('ERR', [
            'function' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);

        foreach ($exception->getTrace() as $index => $entry) {
            $trace .= $this->formatStacktraceEntry(sprintf('#%d', $index), $entry);
        }

        return $trace;
    }

    /**
     * @param string $index
     * @param array $entry
     *
     * @return string
     */
    private function formatStacktraceEntry($index, array $entry)
    {
        $entry = array_replace([
            'file' => '',
            'line' => '?',
            'function' => '',
            'type' => '',
            'class' => '',
            'args' => [],
        ], $entry);

        if ($entry['class']) {
            $function = $entry['class'] . $entry['type'] . $entry['function'];
            $args = $entry['args'];
            array_walk($args, function(&$v) {
                $v = is_object($v) ? get_class($v) : gettype($v);
            });
            $function .= sprintf('(%s)', implode(', ', $args));
        } else {
            $function = $entry['function'];
        }

        $label = str_pad($index, 3);

        $file = $entry['file'] ? sprintf('%s:%s', $entry['file'], $entry['line']) : '[internal function]';
        $file = ($this->root) ? str_replace($this->root, '', $file) : $file;

        $entry = <<<TEXT
$label $file
    $function
TEXT;

        return $entry . str_repeat(PHP_EOL, 2);
    }

    /**
     * @return string
     */
    private function findAppRoot()
    {
        $current = __DIR__;
        if ($cut = strpos($current, 'vendor')) {
            return substr($current, 0, $cut);
        }

        return '';
    }

    /**
     * @return callable
     */
    private function getDefaultHeaderSetter()
    {
        return 'header';
    }
}
