<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\PseudoIntegration;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use function assert;

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
                $compiler->addExtension('extensions', new ExtensionsExtension());
            },
            $key
        );

        $container = new $class();
        assert($container instanceof Container);

        return $container;
    }
}
