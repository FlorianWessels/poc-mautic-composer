<?php

namespace Mautic\Mautic\Core\Composer;


use Composer\Script\Event;
use Mautic\Mautic\Composer\Core\InstallerScriptInterface;
use Mautic\Mautic\Composer\Plugin\Config;
use Symfony\Component\Filesystem\Filesystem;
use Composer\Util\Filesystem as FilesystemUtility;

class CliEntryPoint implements InstallerScriptInterface
{
    private $source;

    private $target;

    public function __construct(string $source, string $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function execute(Event $event): bool
    {
        $composer = $event->getComposer();
        $filesystemUtility = new FilesystemUtility();
        $filesystem = new Filesystem();
        $pluginConfig = Config::load($composer);

        $entryPointContent = file_get_contents($this->source);
        $targetFile = $pluginConfig->get('root-dir') . '/' . $this->target;
        $autoloadFile = $composer->getConfig()->get('vendor-dir') . '/autoload.php';

        $entryPointContent = preg_replace(
            '/__DIR__ . \'[^\']*\'/',
            $filesystemUtility->findShortestPathCode($targetFile, $autoloadFile),
            $entryPointContent
        );

        $filesystemUtility->ensureDirectoryExists(dirname($targetFile));
        $filesystem->dumpFile($targetFile, $entryPointContent);
        $filesystem->chmod($targetFile, 0755);

        return $filesystem->exists($targetFile);
    }
}
