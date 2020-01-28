<?php

namespace Mautic\Mautic\Composer\Installer;

use Composer\Cache;
use Composer\Composer;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Script\ScriptEvents;
use Composer\Script\Event;
use Mautic\Mautic\Composer\Installer\Downloader\MtcDownloader;
use Mautic\Mautic\Composer\Plugin\Config;
use Mautic\Mautic\Composer\Plugin\PluginImplementation;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private $handleEvents = [];

    private $pluginImplementation;

    public static function getSubscribedEvents() {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => ['listen'],
            ScriptEvents::POST_AUTOLOAD_DUMP => ['listen'],
        ];
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->ensureComposerConstraints($io);
        $pluginConfig = Config::load($composer);

        $installationManager = $composer->getInstallationManager();
        $installationManager->addInstaller(new BundleInstaller($io, $composer, $pluginConfig));

        $config = $composer->getConfig();

        if ($config->get('cache-files-ttl') > 0) {
            $cache = new Cache($io, $config->get('cache-files-dir'), 'a-z0-9_./');
        }

        $composer->getDownloadManager()->setDownloader('mtc', new MtcDownloader($io, $config, null, $cache));

        $composer->getEventDispatcher()->addSubscriber($this);
    }

    public function listen(Event $event)
    {
        if (!empty($this->handleEvents[$event->getName()])) {
            return;
        }

        $this->handleEvents[$event->getName()] = true;

        if (!file_exists(__FILE__) && !file_exists(__DIR__) . '/Plugin/PluginImplementation.php') {
            return;
        }

        if ($this->pluginImplementation === null) {
            $this->pluginImplementation = new PluginImplementation($event);
        }

        switch($event->getName()) {
            case ScriptEvents::PRE_AUTOLOAD_DUMP:
                $this->pluginImplementation->preAutoloadDump($event);
                break;

            case ScriptEvents::POST_AUTOLOAD_DUMP:
                $this->pluginImplementation->postAutoloadDump($event);
                break;
        }
    }

    private function ensureComposerConstraints(IOInterface $io)
    {
        if (
            !class_exists('Composer\\Installer\\BinaryInstaller')
            || !interface_exists('Composer\\Installer\\BinaryPresenceInterface')
        ) {
            $io->writeError('');
            $io->writeError(sprintf(
                '<error>Composer version (%s) you are using is too low. Please upgrade Composer to 1.2.0 or higher!</error>',
                Composer::VERSION
            ));
            $io->writeError('<error>Mautic installer plugin will be disabled!</error>');
            throw new \RuntimeException('Mautic Installer disabled!', 1580130773);
        }
    }
}
