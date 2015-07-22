<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;

/**
 * This class requires "symfony/debug".
 *
 * Slim already catches non-fatal errors. This handle can be attached as a shutdown handlerand send super fatals back
 * into Slim to be handled the same as other errors.
 *
 * Example, from index.php:
 * ```
 * use QL\Panthor\ErrorHandler;
 *
 * // Application
 * $app = $container->get('slim');
 *
 * # convert errors to exceptions
 * FatalErrorHandler::register([$app, 'error']);
 *
 * $app->run();
 * ```
 */
class FatalErrorHandler
{
    private static $reservedMemory;
    private static $exceptionHandler;

    public static $levels = array(
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        E_NOTICE => 'Notice',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice',
        E_WARNING => 'Warning',
        E_USER_WARNING => 'User Warning',
        E_COMPILE_WARNING => 'Compile Warning',
        E_CORE_WARNING => 'Core Warning',
        E_USER_ERROR => 'User Error',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_COMPILE_ERROR => 'Compile Error',
        E_PARSE => 'Parse Error',
        E_ERROR => 'Error',
        E_CORE_ERROR => 'Core Error',
    );

    /**
     * Registers the fatal handler to send super fatal errors to a designated exception handler.
     *
     * @param callable $handler
     *
     * @return void
     */
    public static function register(callable $handler)
    {
        if (null === self::$reservedMemory) {
            self::$reservedMemory = str_repeat('x', 10240);
            register_shutdown_function(__CLASS__ . '::handleFatalError');
        }

        self::$exceptionHandler = $handler;
    }

    /**
     * Shutdown registered function for handling PHP fatal errors.
     *
     * @throws Exception
     */
    public static function handleFatalError()
    {
        self::$reservedMemory = '';

        if (!$handler = self::$exceptionHandler) {
            return;
        }

        $error = error_get_last();

        if ($error && $error['type'] &= E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR) {

            $msg = sprintf('%s: %s', self::getErrorType($error['type']), $error['message']);

            if (0 === strpos($error['message'], 'Allowed memory') || 0 === strpos($error['message'], 'Out of memory')) {
                $exception = new OutOfMemoryException($msg, 0, $error['type'], $error['file'], $error['line'], 2, false);
            } else {
                $exception = new FatalErrorException($msg, 0, $error['type'], $error['file'], $error['line'], 2, true);
            }
        }

        if (!isset($exception)) {
            return;
        }

        try {
            $handler($exception);
        } catch (Exception $ex) {
            // Silence any further exceptions
        }
    }

    /**
     * @param int $errorSeverity
     *
     * @return string
     */
    public static function getErrorType($errorSeverity)
    {
        if (isset(self::$levels[$errorSeverity])) {
            return self::$levels[$errorSeverity];
        } else {
            return 'Exception';
        }
    }
}
