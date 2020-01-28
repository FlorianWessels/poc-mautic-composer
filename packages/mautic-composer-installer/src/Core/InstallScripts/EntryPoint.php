<?php

namespace Mautic\Mautic\Composer\Core\InstallScripts;


use Composer\Script\Event;
use Composer\Util\Filesystem;
use Mautic\Mautic\Composer\Core\InstallerScriptInterface;
use Mautic\Mautic\Composer\Plugin\Config;

class EntryPoint implements InstallerScriptInterface
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
        $filesystem = new Filesystem();
        $pluginConfig = Config::load($composer);

        $entryPointContent = file_get_contents($this->source);
        $targetFile = $pluginConfig->get('root-dir') . '/' . $this->target;
        $autoloadFile = $composer->getConfig()->get('vendor-dir') . '/autoload.php';

        $entryPointContent = preg_replace(
            '/__DIR__ . \'[^\']*\'/',
            $filesystem->findShortestPathCode($targetFile, $autoloadFile),
            $entryPointContent
        );

        $filesystem->ensureDirectoryExists(dirname($targetFile));
        file_put_contents($targetFile, $entryPointContent);

        return true;
    }
}
