<?php

namespace Mautic\Mautic\Composer\Core\Token;


use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Mautic\Mautic\Composer\Plugin\Config;

class RootDirToken extends AbstractToken
{
    protected $name = 'root-dir';

    public function getContent(): string
    {
        $includeFileFolder = dirname(__DIR__, 4);
        $filesystem = new Filesystem();

        return $filesystem->findShortestPathCode(
            $includeFileFolder,
            $this->config->get('root-dir'),
            true
        );
    }
}
