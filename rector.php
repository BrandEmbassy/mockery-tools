<?php declare(strict_types = 1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;

$rectorConfigBuilder = RectorConfig::configure();
$defaultRectorConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-rector.php';

$defaultSkipList = $defaultRectorConfigurationSetup($rectorConfigBuilder);

$rectorConfigBuilder
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withCache('./var/temp/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip($defaultSkipList);

return $rectorConfigBuilder;
