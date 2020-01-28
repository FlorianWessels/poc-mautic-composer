<?php

namespace Mautic\Mautic\Composer\Plugin;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Mautic\Mautic\Composer\Core\IncludeFile;
use Mautic\Mautic\Composer\Core\ScriptDispatcher;
use Mautic\Mautic\Composer\Core\Token\ComposerModeToken;
use Mautic\Mautic\Composer\Core\Token\RootDirToken;

class PluginImplementation
{
    private $composer;

    private $scriptDispatcher;

    private $includeFile;

    public function __construct(Event $event, ?ScriptDispatcher $scriptDispatcher = null, ?IncludeFile $includeFile = null)
    {
        $this->composer = $event->getComposer();
        $io = $event->getIO();
        $config = Config::load($this->composer);

        $this->scriptDispatcher = $scriptDispatcher ?? new ScriptDispatcher($event);
        $this->includeFile = $includeFile ?? new IncludeFile(
            $event->getIO(),
                $this->composer,
                [
                    new RootDirToken($io, $config),
                    new ComposerModeToken($io, $config),
                ],
                new Filesystem()
            );
    }

    public function preAutoloadDump(Event $event): void
    {
        $this->includeFile->register();
    }

    public function postAutoloadDump(Event $event): void
    {
        $this->scriptDispatcher->executeScripts();
    }
}
