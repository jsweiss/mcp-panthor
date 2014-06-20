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
    const DEFAULT_README = <<<README
## %s

%s
README;
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
        $packageName = $this->getPackageName($io);
        $description = $this->getDescription($io);

        $this->copyConfiguration($io);

        $this->prepareDists($io, $namespace);
        $this->prepareComposerConfiguration($io, $namespace, $packageName, $description);
        $this->prepareReadme($io, $packageName, $description);

        $io->write('');
        $io->write('Installation almost finished!');
        $io->write('Run "composer update" to finalize dependencies.');
        $io->write('Run "bin/normalize-configuration" to copy dev configuration".');
        $io->write('Run "git init" to create the initial git repository.');
    }

    /**
     * @param Event $event
     *
     * @return null
     */
    public static function run(Event $event)
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
        $copy = [
            'bin' => 'bin',
            'configuration' => 'configuration',
            'public' => 'public',
            'src-application' => 'src',
            'testing' => 'testing'
        ]

        $io->write('Copying application files');

        foreach ($copy as $from => $to) {
            $from = sprintf('%s/%s', $this->root, $from);
            $to = sprintf('%s/%s', $this->appRoot, $to);
            $command = sprintf('cp -R -v "%s" "%s"', $from, $to);
            exec($command);
        }
    }

    /**
     * Get the namespace for the project being created.
     *
     * @param IOInterface $io
     *
     * @return string
     */
    private function getApplicationNamespace(IOInterface $io)
    {
        $io->write('Please enter the namespace of your application.');
        $io->write(sprintf('Example: "%s"', self::DEFAULT_NAMESPACE));

        $namespace = $io->ask('Application namespace: ', self::DEFAULT_NAMESPACE);
        return rtrim($namespace, '\\');
    }

    /**
     * Get the package name for the project being created.
     *
     * @param IOInterface $io
     *
     * @return string
     */
    private function getPackageName(IOInterface $io)
    {
        $io->write('Please enter the package name of your application.');
        $io->write(sprintf('Example: "%s"', self::DEFAULT_PACKAGE_NAME));

        return $io->ask('Application package name: ', self::DEFAULT_PACKAGE_NAME);
    }

    /**
     * Get the descriptionfor the project being created.
     *
     * @param IOInterface $io
     *
     * @return string|null
     */
    private function getDescription(IOInterface $io)
    {
        $io->write('Please enter a description for your application.');
        $io->write('This is optional.');

        return $io->ask('Application description:');
    }

    /**
     * @param IOInterface $io
     * @param string $namespace
     * @param string $packageName
     * @param string|null $description
     *
     * @return null
     */
    private function prepareComposerConfiguration(IOInterface $io, $namespace, $packageName, $description)
    {
        $io->write('Fixing composer.json');

        $filename = $this->appRoot. '/composer.json';

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        $data['name'] = $packageName;
        $data['description'] = ($description) ?: '';
        $data['autoload']['psr-4'] = [
            $namespace . '\\' => 'src',
            $namespace . '\\Testing\\' => 'testing/src'
        ];
        unset($data['scripts']);

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }

    /**
     * @param IOInterface $io
     * @param string $namespace
     *
     * @return null
     */
    private function prepareDists(IOInterface $io, $namespace)
    {
        $files = [
            'bin/dump-di',
            'configuration/di.yml',
            'configuration/bootstrap.php',
            'public/index.php',
            'src/Controller/TestController.php',
            'testing/bootstrap.php',
            'testing/src/TestResponse.php',
            'testing/tests/Controller/TestControllerTest.php',
        ];

        foreach ($files as $file) {
            $io->write(sprintf('Preparing %s', $file));
            $this->prepareDistFile($file, $namespace);
        }
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

        $perm = fileperms($distFilename);
        chmod($targetFilename, $perm);

        unlink($distFilename);
    }

    /**
     * @param IOInterface $io
     * @param string $packageName
     * @param string|null $description
     *
     * @return null
     */
    private function prepareReadme(IOInterface $io, $packageName, $description)
    {
        $io->write('Creating a default README.md');

        $filename = $this->appRoot . '/README.md';
        $description = ($description) ? $description . "\n" : '';
        $contents = sprintf(self::DEFAULT_README, $packageName, $description);

        file_put_contents($filename, $contents);
    }
}
