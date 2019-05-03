<?php declare(strict_types = 1);

namespace BE\MockeryTools;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;

class ContainerFactory
{
    /**
     * @param string[] $configs
     */
    public static function create(
        array $configs,
        string $tempDirectory,
        string $key
    ): Container {
        $loader = new ContainerLoader($tempDirectory, true);

        $class = $loader->load(
            static function (Compiler $compiler) use ($configs): void {
                foreach ($configs as $config) {
                    $compiler->loadConfig($config);
                }
            },
            $key
        );

        /** @var Container $container */
        $container = new $class();

        return $container;
    }
}
