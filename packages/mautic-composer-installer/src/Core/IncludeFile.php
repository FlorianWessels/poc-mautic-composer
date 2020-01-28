<?php

namespace Mautic\Mautic\Composer\Core;


use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Mautic\Mautic\Composer\Core\Token\AbstractToken;

class IncludeFile
{
    private const INCLUDE_FILE = '/mautic/autoload-include.php';

    private const INCLUDE_FILE_TEMPLATE = '/res/php/autoload-include.tmpl.php';

    private $io;

    private $composer;

    /**
     * @var AbstractToken[]
     */
    private $tokens = [];

    private $filesystem;

    public function __construct(IOInterface $io, Composer $composer, array $tokens, Filesystem $filesystem = null)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->tokens = $tokens;
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function register()
    {
        $this->io->write(
            '<info>Register mautic/mautic-composer-installer file in root package autoload definition</info>',
            true,
            IOInterface::VERBOSE
            );

        $includeFile = $this->composer->getConfig()->get('vendor-dir') . self::INCLUDE_FILE;
        file_put_contents($includeFile, $this->getIncludeFileContent());

        $rootPackage = $this->composer->getPackage();
        $autoloadDefinition = $rootPackage->getAutoload();
        $autoloadDefinition['files'][] = $includeFile;
        $rootPackage->setAutoload($autoloadDefinition);

        // Load it to expose the paths to further plugin functionality
        require $includeFile;
    }

    protected function getIncludeFileContent()
    {
        $includeFileTemplate = $this->filesystem->normalizePath(dirname(__DIR__, 2) . self::INCLUDE_FILE_TEMPLATE);
        $includeFileContent = file_get_contents($includeFileTemplate);

        foreach ($this->tokens as $token) {
            $includeFileContent = self::replaceToken($token->getName(), $token->getContent(), $includeFileContent);
        }

        return $includeFileContent;
    }

    protected static function replaceToken(string $name, string $content, string $subject): string
    {
        return str_replace(sprintf('\'{$%s}\'', $name), $content, $subject);
    }
}
