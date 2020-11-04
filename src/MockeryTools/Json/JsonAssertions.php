<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Json;

use BrandEmbassy\MockeryTools\FileLoader;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;

final class JsonAssertions
{
    public static function assertJsonEqualsFile(
        string $expectedJson,
        string $filePath,
        array $valuesToReplace = []
    ): void {
        $jsonString = FileLoader::loadJsonStringFromJsonFileAndReplace($filePath, $valuesToReplace);
        Assert::assertJsonStringEqualsJsonString($expectedJson, $jsonString);
    }


    public static function assertArrayEqualsFile(
        array $expectedArray,
        string $filePath,
        array $valuesToReplace = []
    ): void {
        $jsonString = FileLoader::loadJsonStringFromJsonFileAndReplace($filePath, $valuesToReplace);
        Assert::assertJsonStringEqualsJsonString(Json::encode($expectedArray), $jsonString);
    }
}
