<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor;

use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class SymfonyIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function testContainerCompiles()
    {
        $configRoot = __DIR__ . '/../../configuration';

        $container = new ContainerBuilder;
        $builder = new YamlFileLoader($container, new FileLocator($configRoot));
        $builder->load('panthor.yml');

        $container->compile();
    }
}
