<?php
/**
 * @copyright ©2014 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\Setup;

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
        // ask IO for this
        $namespace = $io->ask('Please enter the namespace of your application.',  'QL\SampleApplication');

        $this->copyConfiguration();

        $this->sanitizeDistFile('bin/dump-di', $namespace);
        $this->sanitizeDistFile('configuration/bootstrap.php', $namespace);
        $this->sanitizeDistFile('public/index.php', $namespace);

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
     * @return null
     */
    private function copyConfiguration()
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
     * Sanitize dist file.
     *
     * @param string $filename
     * @param string $namespace
     * @return null
     */
    private function sanitizeDistFile($filename, $namespace)
    {
        $io->write('Preparing ' . $filename);
        $targetFilename = $this->appRoot. '/' . $filename;
        $distFilename = $targetFilename . '.dist';

        $contents = file_get_contents($distFilename);
        $contents = str_replace('{{ application.namespace }}', $namespace, $contents);

        file_put_contents($targetFilename, $contents);
        unlink($distFilename);
    }
}
