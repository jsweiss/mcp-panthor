<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Bootstrap;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Utility to handle container building and caching.
 */
class Di
{
    /**
     * The primary configuration file. The entry point of all configuration.
     *
     * @type string
     */
    const PRIMARY_CONFIGURATION_FILE = 'configuration/config.yml';

    /**
     * @param string $root The application root directory.
     *
     * @return ContainerBuilder
     */
    public static function buildDi($root)
    {
        $container = new ContainerBuilder;
        $builder = new YamlFileLoader($container, new FileLocator($root));
        $builder->load(static::PRIMARY_CONFIGURATION_FILE);
        $container->compile();

        return $container;
    }

    /**
     * @param ContainerBuilder $container  The built container, ready for caching.
     * @param string           $class      Fully qualified class name of the cached container.
     * @param array            $config     Optionally pass additional configuration to the Dumper
     *
     * @return string The cached container file contents.
     */
    public static function dumpDi(ContainerBuilder $container, $class, array $config = [])
    {
        $exploded = explode('\\', $class);
        $config = array_merge($config, [
            'class' => array_pop($exploded),
            'namespace' => implode('\\', $exploded)
        ]);

        return (new PhpDumper($container))->dump($config);
    }

    /**
     * @param  string $root  The application root directory.
     * @param  string $class Fully qualified class name of the cached container.
     *
     * @return ContainerInterface A service container. This may or may not be a cached container.
     */
    public static function getDi($root, $class)
    {
        $root = rtrim($root, '/');

        if (class_exists($class)) {
            $container = new $class;

            // Force a fresh container in debug mode
            if ($container->hasParameter('debug') && $container->getParameter('debug')) {
                $container = static::buildDi($root);
            }
        } else {
            $container = static::buildDi($root);
        }

        // Set the synthetic root service. This must not ever be cached.
        $container->set('root', $root);

        return $container;
    }
}
