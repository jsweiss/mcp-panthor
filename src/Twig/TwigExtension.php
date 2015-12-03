<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\Panthor\Twig;

use QL\Panthor\Utility\Url;
use Twig_Extension;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension
{
    /**
     * @type Url
     */
    private $url;

    /**
     * @type bool
     */
    private $isDebugMode;

    /**
     * @param Url $url
     * @param bool $isDebugMode
     */
    public function __construct(Url $url, $isDebugMode)
    {
        $this->url = $url;

        $this->isDebugMode = (bool) $isDebugMode;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'panthor';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('urlFor', [$this->url, 'urlFor']),
            new Twig_SimpleFunction('route', [$this->url, 'currentRoute']),

            new Twig_SimpleFunction('isDebugMode', [$this, 'isDebugMode'])
        ];
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return ($this->isDebugMode);
    }
}
