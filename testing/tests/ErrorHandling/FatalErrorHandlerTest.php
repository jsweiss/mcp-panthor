<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use PHPUnit_Framework_TestCase;

class FatalErrorHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testErrorSeverityInHuman()
    {
        $this->assertSame('Deprecated', FatalErrorHandler::getErrorType(\E_DEPRECATED));
        $this->assertSame('User Deprecated', FatalErrorHandler::getErrorType(\E_USER_DEPRECATED));

        $this->assertSame('Notice', FatalErrorHandler::getErrorType(\E_NOTICE));
        $this->assertSame('User Notice', FatalErrorHandler::getErrorType(\E_USER_NOTICE));
        $this->assertSame('Runtime Notice', FatalErrorHandler::getErrorType(\E_STRICT));

        $this->assertSame('Warning', FatalErrorHandler::getErrorType(\E_WARNING));
        $this->assertSame('User Warning', FatalErrorHandler::getErrorType(\E_USER_WARNING));
        $this->assertSame('Compile Warning', FatalErrorHandler::getErrorType(\E_COMPILE_WARNING));
        $this->assertSame('Core Warning', FatalErrorHandler::getErrorType(\E_CORE_WARNING));

        $this->assertSame('User Error', FatalErrorHandler::getErrorType(\E_USER_ERROR));
        $this->assertSame('Catchable Fatal Error', FatalErrorHandler::getErrorType(\E_RECOVERABLE_ERROR));

        $this->assertSame('Compile Error', FatalErrorHandler::getErrorType(\E_COMPILE_ERROR));
        $this->assertSame('Parse Error', FatalErrorHandler::getErrorType(\E_PARSE));
        $this->assertSame('Error', FatalErrorHandler::getErrorType(\E_ERROR));
        $this->assertSame('Core Error', FatalErrorHandler::getErrorType(\E_CORE_ERROR));

    }
}
