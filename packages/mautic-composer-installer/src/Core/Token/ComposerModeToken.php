<?php

namespace Mautic\Mautic\Composer\Core\Token;


use Composer\IO\IOInterface;

class ComposerModeToken extends AbstractToken
{
    protected $name = 'composer-mode';

    public function getContent(): string
    {
        $this->io->writeError('<info>Inserting MAUTIC_COMPOSER_MODE constant</info>', true, IOInterface::VERBOSE);

        return <<<PHP
Mautic is installed via composer. Flag this with a constant.

if (!defined('MAUTIC_COMPOSER_MODE')) {
    define('MAUTIC_COMPOSER_MODE', true);
}
PHP;
    }
}
