<?php

namespace Mautic\Mautic\Core\Composer;


use Composer\Script\Event;
use Mautic\Mautic\Composer\Core\InstallerScriptRegistrationInterface;
use Mautic\Mautic\Composer\Core\InstallScripts\EntryPoint;
use Mautic\Mautic\Composer\Core\ScriptDispatcher;

class InstallerScripts implements InstallerScriptRegistrationInterface
{
    public static function register(Event $event, ScriptDispatcher $scriptDispatcher)
    {
        $baseDirectory = dirname(__DIR__, 2) . '/Installer';

        $scriptDispatcher->addInstallerScript(
            new CliEntryPoint(
                $baseDirectory . '/cli.php',
                'bin/console'
            ), 0
        );

        $files = [
            100 => '.htaccess',
            101 => 'favicon.ico',
            102 =>'index.php',
            103 =>'index_dev.php',
            104 =>'offline.php',
            105 =>'robots.txt',
            106 =>'upgrade.php',
            200 => 'system/.htaccess',
            201 => 'system/AppCache.php',
            202 => 'system/AppKernel.php',
            203 => 'system/autoload.php',
            204 => 'system/version.txt',
        ];

        foreach ($files as $priority => $file) {
            $scriptDispatcher->addInstallerScript(
                new EntryPoint(
                    $baseDirectory . '/' . $file,
                    $file
                ), $priority
            );
        }
    }
}
