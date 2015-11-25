<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Exception;

use PHPUnit_Framework_TestCase;
use QL\Panthor\HTTPProblem\HTTPProblem;

class HTTPProblemExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException QL\Panthor\Exception\Exception
     */
    public function test()
    {
        throw new HTTPProblemException(403, 'Forbidden');
    }

    public function testExceptionCreatesProblem()
    {
        $extensions = [
            'data1' => '1234',
            'data2' => 'abcd'
        ];

        $exception = new HTTPProblemException(500, 'An error occurred.', $extensions);

        $this->assertInstanceof(HTTPProblem::CLASS, $exception->problem());

        $this->assertSame(500, $exception->problem()->status());
        $this->assertSame('Internal Server Error', $exception->problem()->title());
        $this->assertSame('An error occurred.', $exception->problem()->detail());
        $this->assertSame($extensions, $exception->problem()->extensions());
    }
}
