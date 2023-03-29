<?php declare(strict_types = 1);

use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$defaultEcsConfigurationSetup = require 'vendor/brandembassy/coding-standard/default-ecs.php';

return static function (ECSConfig $ecsConfig) use ($defaultEcsConfigurationSetup): void {
    $defaultSkipList = $defaultEcsConfigurationSetup($ecsConfig, __DIR__);

    $ecsConfig->paths([
        'src',
        'ecs.php',
    ]);

    $skipList = [];

    if (PHP_VERSION_ID < 81000) {
        $skipList[ParameterTypeHintSniff::class . '.UselessSuppress'] = [
            'src/MockeryTools/Arrays/ArraySubsetConstraint.php',
            'src/MockeryTools/Arrays/StrictArrayMatcher.php',
            'src/MockeryTools/DateTime/DateTimeAsAtomMatcher.php',
            'src/MockeryTools/DateTime/DateTimeAsTimestampMatcher.php',
            'src/MockeryTools/DateTime/DateTimeZoneNameMatcher.php',
            'src/MockeryTools/Enum/EnumValueMatcher.php',
            'src/MockeryTools/Http/HttpRequestMatcher.php',
            'src/MockeryTools/Http/HttpRequestOptionsMatcher.php',
            'src/MockeryTools/String/StringStartsWithMatcher.php',
            'src/MockeryTools/String/UuidMatcher.php',
            'src/MockeryTools/Uri/UriMatcher.php',
        ];
    }

    $ecsConfig->skip(array_merge($defaultSkipList, $skipList));
};
