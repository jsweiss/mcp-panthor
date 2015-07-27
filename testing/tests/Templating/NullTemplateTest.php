<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Templating;

use PHPUnit_Framework_TestCase;

class NullTemplateTest extends PHPUnit_Framework_TestCase
{
    public function testNullTemplateRendersEmptyString()
    {
        $template = new NullTemplate;

        $rendered = $template->render([
            'param1' => 'abcd',
            'param2' => '1234',
        ]);

        $this->assertSame('', $rendered);
    }
}
