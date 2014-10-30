<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use DateTime;
use DateTimeZone;
use MCP\DataType\Time\TimePoint;
use Mockery;
use PHPUnit_Framework_TestCase;
use stdClass;

class TwigExtensionTest extends PHPUnit_Framework_TestCase
{
    public $url;
    public $clock;

    public function setUp()
    {
        $this->url = Mockery::mock('QL\Panthor\Utility\Url');
        $this->clock = Mockery::mock('MCP\DataType\Time\Clock');
    }

    public function testName()
    {
        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', false);
        $this->assertSame('panthor', $ext->getName());
    }

    public function testIsDebugMode()
    {
        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', true);
        $this->assertSame(true, $ext->isDebugMode());
    }

    public function testGetFunctionsDoesNotBlowUp()
    {
        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', false);
        $this->assertInternalType('array', $ext->getFunctions());
    }

    public function testGetFiltersDoesNotBlowUp()
    {
        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', false);
        $this->assertInternalType('array', $ext->getFilters());
    }

    public function testGetTimepointReadsFromClock()
    {
        $time = Mockery::mock('MCP\DataType\Time\TimePoint');
        $this->clock
            ->shouldReceive('read')
            ->andReturn($time);

        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', false);
        $this->assertSame($time, $ext->getTimepoint());
    }

    public function testGetTimepointReadsFromClockAndModifies()
    {
        $time = Mockery::mock('MCP\DataType\Time\TimePoint');
        $time
            ->shouldReceive('modify')
            ->with('+2 days')
            ->once();

        $this->clock
            ->shouldReceive('read')
            ->andReturn($time);

        $ext = new TwigExtension($this->url, $this->clock, 'America\Detroit', false);
        $this->assertSame($time, $ext->getTimepoint('+2 days'));
    }

    public function testFormattingDateAcceptsDateTime()
    {
        $expected = '2014-08-05 11:00:32';
        $datetime = new DateTime('2014-08-05 15:00:32', new DateTimeZone('UTC'));

        $ext = new TwigExtension($this->url, $this->clock, 'America/Detroit', false);
        $this->assertSame($expected, $ext->formatTimepoint($datetime, 'Y-m-d H:i:s'));
    }

    public function testFormattingDateAcceptsTimePoint()
    {
        $expected = '2014-08-05 15:00:32';
        $timepoint = new TimePoint(2014, 8, 5, 15, 0, 32, 'America/Detroit');

        $ext = new TwigExtension($this->url, $this->clock, 'America/Detroit', false);
        $this->assertSame($expected, $ext->formatTimepoint($timepoint, 'Y-m-d H:i:s'));
    }

    public function testFormattingDateFailsGracefullyWithUnknownType()
    {
        $invalid = new stdClass;

        $ext = new TwigExtension($this->url, $this->clock, 'America/Detroit', false);
        $this->assertSame('', $ext->formatTimepoint($invalid, 'Y-m-d H:i:s'));
    }
}
