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
     * @return null
     */
    public function __invoke(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        $io->write('Root: ' . $this->root);
        $io->write('Application Root: ' . $this->appRoot);

        // app settings
        $namespace = $this->getApplicationNamespace($io);

        $this->copyConfiguration($io);
        $this->sanitizeDists($io, $namespace);

        // overwrite composer.json
        // ask user for package name
    }

    /**
     * @param Event $event
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
     * @return null
     */
    private function getApplicationNamespace(IOInterface $io)
    {
        $io->write('Please enter the namespace of your application:');
        $io->write('Examples: "QL\SampleApplication", "QL\SubNamespace\Example"');

        $namespace = $io->ask('Application namespace: ', 'QL\SampleApplication');
        return rtrim($namespace, '\\');
    }

    /**
     * @param IOInterface $io
     * @param string $namespace
     * @return null
     */
    private function sanitizeDists(IOInterface $io, $namespace)
    {
        $io->write('Preparing bin/dump-di');
        $this->sanitizeDistFile('bin/dump-di', $namespace);

        $io->write('Preparing configuration/bootstrap.php');
        $this->sanitizeDistFile('configuration/bootstrap.php', $namespace);

        $io->write('Preparing public/index.php');
        $this->sanitizeDistFile('public/index.php', $namespace);
    }

    /**
     * Sanitize dist file.
     *
     * @param string $filename
     * @param string $namespace
     * @return null
     */
    private function sanitizeDistFile($filename, $namespace)
    {
        $targetFilename = $this->appRoot. '/' . $filename;
        $distFilename = $targetFilename . '.dist';

        $contents = file_get_contents($distFilename);
        $contents = str_replace('{{ application.namespace }}', $namespace, $contents);

        file_put_contents($targetFilename, $contents);
        unlink($distFilename);
    }
}
