<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use DateTime;
use DateTimeZone;
use MCP\DataType\Time\Clock;
use MCP\DataType\Time\TimePoint;
use QL\Panthor\Utility\Url;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension
{
    /**
     * @type Url
     */
    private $url;

    /**
     * @type Clock
     */
    private $clock;

    /**
     * @type string
     */
    private $displayTimezone;

    /**
     * @type boolean
     */
    private $isDebugMode;

    /**
     * @param Url $url
     * @param Session $session
     * @param CsrfManager $csrf
     * @param Clock $clock
     * @param string $timezone
     * @param boolean $isDebugMode
     */
    public function __construct(Url $url, Clock $clock, $timezone, $isDebugMode)
    {
        $this->url = $url;
        $this->clock = $clock;

        $this->displayTimezone = $timezone;
        $this->isDebugMode = $isDebugMode;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sso';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('timepoint', [$this, 'formatTimePoint']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('urlFor', [$this->url, 'urlFor']),
            new Twig_SimpleFunction('route', [$this->url, 'route']),

            new Twig_SimpleFunction('isDebugMode', [$this, 'isDebugMode']),

            new Twig_SimpleFunction('timepoint', [$this, 'getTimepoint'])
        ];
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return ($this->isDebugMode);
    }

    /**
     * @param string|null $modifier
     *
     * @return TimePoint
     */
    public function getTimepoint($modifier = null)
    {
        $now = $this->clock->read();

        if ($modifier) {
            $now->modify($modifier);
        }

        return $now;
    }

    /**
     * Format a DateTime or TimePoint. Invalid values will output an empty string.
     *
     * @param TimePoint|DateTime|null $time
     * @param string $format
     *
     * @return string
     */
    public function formatTimepoint($time, $format)
    {
        if ($time instanceof TimePoint) {
            return $time->format($format, $this->displayTimezone);
        }

        if ($time instanceof DateTime) {
            $formatted = clone $time;
            $formatted->setTimezone(new DateTimeZone($this->displayTimezone));
            return $formatted->format($format);
        }

        return '';
    }
}
