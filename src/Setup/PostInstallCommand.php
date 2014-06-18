<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Setup;

use Composer\IO\IOInterface;
use Composer\Script\Event;

class PostInstallCommand
{
    const DEFAULT_NAMESPACE = 'QL\SampleApplication';
    const DEFAULT_PACKAGE_NAME = 'ql/sample-application';

    /**
     * The root directory of this package after it is installed.
     *
     * @type string
     */
    private $root;

    /**
     * The root directory of the base application.
     *
     * @type string
     */
    private $appRoot;

    /**
     * @param string $root
     * @param string $appRoot
     */
    public function __construct($root, $appRoot)
    {
        $this->root = $root;
        $this->appRoot = $appRoot;
    }

    /**
     * @param Event $event
     *
     * @return null
     */
    public function __invoke(Event $event)
    {
        $io = $event->getIO();

        // app settings
        $namespace = $this->getApplicationNamespace($io);

        $this->copyConfiguration($io);
        $this->prepareDists($io, $namespace);

        $this->prepareComposerConfiguration($io, $namespace);
    }

    /**
     * @param Event $event
     *
     * @return null
     */
    public static function derp(Event $event)
    {
        $root = __DIR__ . '/../..';
        $appRoot = getcwd();

        $command = new static($root, $appRoot);
        call_user_func($command, $event);
    }

    /**
     * Copy required configuration to the application.
     *
     * @param IOInterface $io
     *
     * @return null
     */
    private function copyConfiguration(IOInterface $io)
    {
        $cmdBin = sprintf('cp -R -v "%s/bin" "%s/bin"', $this->root, $this->appRoot);
        $cmdConfig = sprintf('cp -R -v "%s/configuration" "%s/configuration"', $this->root, $this->appRoot);
        $cmdPublic = sprintf('cp -R -v "%s/public" "%s/public"', $this->root, $this->appRoot);

        // copy bin/, configuration/, public/
        $io->write('Copying application files');
        exec($cmdBin);
        exec($cmdConfig);
        exec($cmdPublic);
    }

    /**
     * Get the namespace for the project being created.
     *
     * @param IOInterface $io
     *
     * @return null
     */
    private function getApplicationNamespace(IOInterface $io)
    {
        $io->write('Please enter the namespace of your application.');
        $io->write('Examples: "QL\SampleApplication", "QL\SubNamespace\Example"');

        $namespace = $io->ask('Application namespace: ', self::DEFAULT_NAMESPACE);
        return rtrim($namespace, '\\');
    }

    /**
     * @param IOInterface $io
     * @param string $namespace
     *
     * @return null
     */
    private function prepareComposerConfiguration(IOInterface $io, $namespace)
    {
        $filename = $this->appRoot. '/composer.json';

        $io->write('Please enter the package name of your application.');
        $io->write('Example: "ql/sample-application"');

        $packageName = $io->ask('Application package name: ', self::DEFAULT_PACKAGE_NAME);

        $json = file_get_contents($filename);
        $data = json_decode($json);

        $data['name'] = $packageName;
        $data['description'] = '';
        $data['autoload']['psr-4'] = [$namespace => 'src'];
        unset($data['scripts']);

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @param IOInterface $io
     * @param string $namespace
     *
     * @return null
     */
    private function prepareDists(IOInterface $io, $namespace)
    {
        $io->write('Preparing bin/dump-di');
        $this->prepareDistFile('bin/dump-di', $namespace);

        $io->write('Preparing configuration/bootstrap.php');
        $this->prepareDistFile('configuration/bootstrap.php', $namespace);

        $io->write('Preparing public/index.php');
        $this->prepareDistFile('public/index.php', $namespace);
    }

    /**
     * Prepare dist file.
     *
     * @param string $filename
     * @param string $namespace
     *
     * @return null
     */
    private function prepareDistFile($filename, $namespace)
    {
        $targetFilename = $this->appRoot. '/' . $filename;
        $distFilename = $targetFilename . '.dist';

        $contents = file_get_contents($distFilename);
        $contents = str_replace('{{ application.namespace }}', $namespace, $contents);

        file_put_contents($targetFilename, $contents);
        unlink($distFilename);
    }
}
