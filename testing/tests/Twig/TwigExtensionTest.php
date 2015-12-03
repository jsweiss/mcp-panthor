<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Twig;

use Mockery;
use PHPUnit_Framework_TestCase;
use QL\Panthor\Utility\Url;

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
