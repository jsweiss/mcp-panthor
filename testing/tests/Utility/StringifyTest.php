<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Utility;

use PHPUnit_Framework_TestCase;

class StringifyTest extends PHPUnit_Framework_TestCase
{
    public function testTemplate()
    {
        $params = [
            'dev',
            'staging',
            'prod'
        ];

        $actual = Stringify::template('%s-%s/%s', $params);

        $this->assertSame('dev-staging/prod', $actual);
    }

    public function testCombine()
    {
        $params = [
            'dev',
            'staging',
            'prod'
        ];

        $actual = Stringify::combine($params);

        $this->assertSame('devstagingprod', $actual);
    }
}
