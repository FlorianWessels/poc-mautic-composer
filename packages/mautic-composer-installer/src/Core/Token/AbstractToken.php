<?php

namespace Mautic\Mautic\Composer\Core\Token;


use Composer\IO\IOInterface;
use Mautic\Mautic\Composer\Plugin\Config;

abstract class AbstractToken
{
    protected $name;

    protected $io;

    protected $config;

    public function __construct(IOInterface $io, Config $config)
    {
        $this->io = $io;
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function getContent(): string;
}
