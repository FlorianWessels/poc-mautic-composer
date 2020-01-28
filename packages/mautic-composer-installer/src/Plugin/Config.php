<?php

namespace Mautic\Mautic\Composer\Plugin;

use Composer\Composer;

class Config
{
    const RELATIVE_PATHS = 1;

    public static $defaultConfig = [
        'web-dir' => 'public',
        'root-dir' => '{$web-dir}',
        'app-dir' => '{$base-dir}',
        'composer-mode' => true,
    ];

    protected $config = [];

    protected $baseDir = '';

    public function __construct(string $baseDir = '')
    {
        $this->baseDir = $baseDir;
        $this->config = static::$defaultConfig;
    }

    /**
     * @param Composer $composer
     *
     * @return Config
     * @throws \ReflectionException
     */
    public static function load(Composer $composer): Config
    {
        static $config;

        if ($config === null) {
            $baseDir = static::extractBaseDir($composer->getConfig());
            $config = new static($baseDir);
            $rootPackageExtraConfig = $composer->getPackage()->getExtra();

            $config->merge($rootPackageExtraConfig);
        }

        return $config;
    }

    public function merge(array $config): void
    {
        if (!empty($config['mautic/mautic'])) {
            foreach ($config['mautic/mautic'] ?? [] as $key => $value) {
                $this->config[$key] = $value;
            }
        }
    }

    public function get(string $key, int $flags = 0)
    {
        switch ($key) {
            case 'root-dir':
            case 'web-dir':
                $value = rtrim($this->process($this->config[$key], $flags), '/\\');
                return ($flags & self::RELATIVE_PATHS) ? $value : $this->realpath($value);
            case 'base-dir':
                return ($flags & self::RELATIVE_PATHS === 1) ? '' : $this->realpath($this->baseDir);
            default:
                return '';
        }
    }

    /**
     * @param \Composer\Config $config
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function extractBaseDir(\Composer\Config $config)
    {
        $reflectionClass = new \ReflectionClass($config);
        $reflectionProperty = $reflectionClass->getProperty('baseDir');
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($config);
    }

    protected function process(string $value, int $flags): string
    {
        $config = $this;

        return preg_replace_callback('#\{\$(.+)\}#',
            function ($match) use ($config, $flags) {
                return $config->get($match[1], $flags);
            },
            $value
        );
    }

    protected function realpath(string $path): string
    {
        if ($path === '') {
            return $this->baseDir;
        }
        if ($path[0] === '/' || (!empty($path[1]) && $path[1] === ':')) {
            return $path;
        }

        return $this->baseDir . '/' . $path;
    }
}
