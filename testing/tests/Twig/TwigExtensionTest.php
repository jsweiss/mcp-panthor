<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use Mockery;
use PHPUnit_Framework_TestCase;

class TwigExtensionTest extends PHPUnit_Framework_TestCase
{
    public $url;

    public function setUp()
    {
        $this->url = Mockery::mock(Url::CLASS);
    }

    public function testName()
    {
        $ext = new TwigExtension($this->url, false);
        $this->assertSame('panthor', $ext->getName());
    }

    public function testIsDebugMode()
    {
        $ext = new TwigExtension($this->url, true);
        $this->assertSame(true, $ext->isDebugMode());
    }

    public function testGetFunctionsDoesNotBlowUp()
    {
        $ext = new TwigExtension($this->url, false);
        $this->assertInternalType('array', $ext->getFunctions());
    }

    public function testGetFiltersDoesNotBlowUp()
    {
        $ext = new TwigExtension($this->url, false);
        $this->assertInternalType('array', $ext->getFilters());
    }
}
