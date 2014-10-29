<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Twig;

use Twig_Environment;

/**
 * This configurator is used to customize the Twig Environment after it is built.
 *
 * You may customize your twig environment further by extending this class and replacing the "applicationConfigure"
 * method.
 */
class EnvironmentConfigurator
{
    /**
     * @type bool
     */
    private $debugMode;

    /**
     * @type string
     */
    private $cacheDir;

    /**
     * @param bool $debugMode
     * @param string $cacheDir
     */
    public function __construct($debugMode, $cacheDir)
    {
        $this->debugMode = $debugMode;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param Twig_Environment $environment
     *
     * @return null
     */
    public function configure(Twig_Environment $environment)
    {
        if ($this->debugMode) {
            $environment->enableDebug();
            $environment->enableAutoReload();

        } else {
            $environment->disableDebug();
            $environment->disableAutoReload();
            $environment->setCache($this->cacheDir);
        }

        $this->applicationConfigure($environment);
    }

    /**
     * Extend and override this method if you wish to customize twig for your application.
     *
     * @param Twig_Environment $environment
     *
     * @return null
     */
    protected function applicationConfigure(Twig_Environment $environment)
    {
    }
}
