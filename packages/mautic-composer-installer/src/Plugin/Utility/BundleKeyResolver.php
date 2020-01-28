<?php
declare(strict_types=1);
namespace Mautic\Mautic\Composer\Plugin\Utility;


use Composer\Package\PackageInterface;
use Mautic\Mautic\Composer\Installer\BundleInstaller;

class BundleKeyResolver
{
    public static function resolve(PackageInterface $package): string
    {
        $bundleKey = '';
        $extra = $package->getExtra();
        $type = $package->getType();

        foreach ($package->getReplaces() as $link) {
            $target = $link->getTarget();
            if (mb_strpos($target, '/') === false) {
                $bundleKey = trim($target);
                break;
            }
        }

        if (empty($bundleKey)) {
            [$_, $bundleKey] = explode('/', $package->getName(), 2);
            $bundleKey = str_replace('-', '_', $bundleKey);
        }

        if (!empty($extra['installer-name'])) {
            $bundleKey = $extra['installer-name'];
        }

        if (!empty($extra['mautic/mautic']['bundle-key'])) {
            $bundleKey = $extra['mautic/mautic']['bundle-key'];
        }

        if (($type === BundleInstaller::TYPE_PLUGIN || $type === BundleInstaller::TYPE_FRAMEWORK) && mb_strpos($bundleKey, 'Bundle') === false) {

            if (mb_strpos($bundleKey, '_') !== false) {
                $parts = explode('_', $bundleKey);
                $bundleKey = '';

                foreach ($parts as $part) {
                    $part = ucfirst($part);
                    if ($part !== 'Bundle') {
                        $bundleKey .= $part;
                    }
                }
            }

            $bundleKey = $bundleKey . 'Bundle';
        }

        return ucfirst($bundleKey);
    }
}
