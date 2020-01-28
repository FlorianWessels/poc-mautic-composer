<?php

namespace Mautic\Mautic\Composer\Installer;


use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Mautic\Mautic\Composer\Plugin\Config;
use Mautic\Mautic\Composer\Plugin\Utility\BundleKeyResolver;

class BundleInstaller extends LibraryInstaller
{
    private $bundleDirectory;

    private $systemBundleDirectory;

    private $themeDirectory;

    public const TYPE_FRAMEWORK = 'mautic-framework';

    public const TYPE_PLUGIN = 'mautic-plugin';

    public const TYPE_THEME = 'mautic-theme';

    private const ALLOWED_PACKAGE_TYPES = [
        self::TYPE_FRAMEWORK,
        self::TYPE_PLUGIN,
        self::TYPE_THEME,
    ];

    public function __construct(IOInterface $io, Composer $composer, Config $config = null)
    {
        parent::__construct($io, $composer);

        $config = $config ?? Config::load($composer);
        $rootDirectory = $this->filesystem->normalizePath($config->get('root-dir'));
        $this->bundleDirectory = $rootDirectory . '/plugins';
        $this->themeDirectory = $rootDirectory . '/themes';
        $this->systemBundleDirectory = $rootDirectory . '/system/bundles';
    }

    public function supports($packageType)
    {
        return in_array($packageType, self::ALLOWED_PACKAGE_TYPES);
    }

    public function getInstallPath(PackageInterface $package)
    {
        $bundleInstallDirectory = BundleKeyResolver::resolve($package);

        switch ($package->getType()) {
            case self::TYPE_FRAMEWORK:
                $directory = $this->systemBundleDirectory;
                break;
            case self::TYPE_PLUGIN:
                $directory = $this->bundleDirectory;
                break;
            case self::TYPE_THEME:
                $directory = $this->themeDirectory;
                break;
            default:
                throw new \RuntimeException('Invalid type.');
        }

        return $directory . DIRECTORY_SEPARATOR . $bundleInstallDirectory;
    }
}
