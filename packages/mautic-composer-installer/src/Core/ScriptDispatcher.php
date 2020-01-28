<?php

namespace Mautic\Mautic\Composer\Core;


use Composer\Autoload\ClassLoader;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Mautic\Mautic\Composer\Core\Exception\StopInstallerScriptExecutionException;

class ScriptDispatcher
{
    private $event;

    /**
     * @var ClassLoader
     */
    private $loader;

    /**
     * @var InstallerScriptInterface[]
     */
    private $installerScripts = [];

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function addInstallerScript(InstallerScriptInterface $script, int $priority = 50)
    {
        $this->installerScripts[$priority] = $script;
    }

    public function executeScripts()
    {
        $io = $this->event->getIO();
        $this->registerLoader();

        ksort($this->installerScripts, SORT_NUMERIC);
        $io->writeError('<info>Executing Mautic installer scripts.</info>', true, IOInterface::VERBOSE);

        try {
            foreach (array_reverse($this->installerScripts) as $installerScript) {
                $io->writeError(sprintf('<info>Executing %s</info>', get_class($installerScript)), true, IOInterface::VERBOSE);

                if (!$installerScript->execute($this->event)) {
                    $io->writeError(sprintf('<error>Executing "%s" failed.</error>', get_class($installerScript)), true);
                }
            }
        } catch (StopInstallerScriptExecutionException $e) {

        } finally {
            $this->unregisterLoader();
        }
    }

    protected function registerLoader()
    {
        $composer = $this->event->getComposer();
        $generator = $composer->getAutoloadGenerator();
        $package = $composer->getPackage();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $packageMap = $generator->buildPackageMap($composer->getInstallationManager(), $package, $packages);
        $autoloads = $generator->parseAutoloads($packageMap, $package);

        $this->loader = $generator->createLoader($autoloads);
        $this->loader->register();

        $this->registerInstallerScripts(array_keys($autoloads['psr-4'] ?? []));
    }

    protected function registerInstallerScripts(array $psr4Namespaces)
    {
        foreach ($psr4Namespaces as $namespace) {
            $scriptsRegistrationCandidate = $namespace . 'Composer\\InstallerScripts';
            if (class_exists($scriptsRegistrationCandidate) && in_array(InstallerScriptRegistrationInterface::class, class_implements($scriptsRegistrationCandidate), true)) {
                $scriptsRegistrationCandidate::register($this->event, $this);
            }
        }
    }

    protected function unregisterLoader()
    {
        $this->loader->unregister();
    }
}
