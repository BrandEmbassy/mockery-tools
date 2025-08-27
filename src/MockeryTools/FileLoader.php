<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools;

use BrandEmbassy\MockeryTools\Json\JsonValuesReplacer;
use LogicException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;
use function assert;
use function is_array;
use function is_object;

/**
 * @final
 */
class FileLoader
{
    /**
     * @return mixed[]
     */
    public static function loadArrayFromJsonFile(string $jsonFilePath): array
    {
        return self::loadArrayFromJsonFileAndReplace($jsonFilePath, []);
    }


    public static function loadObjectFromJsonFile(string $jsonFilePath): stdClass
    {
        return self::loadObjectFromJsonFileAndReplace($jsonFilePath, []);
    }


    /**
     * @param array<string, int|float|bool|string|null> $valuesToReplace
     *
     * @return mixed[]
     */
    public static function loadArrayFromJsonFileAndReplace(string $jsonFilePath, array $valuesToReplace): array
    {
        $fileContents = self::loadJsonStringFromJsonFileAndReplace($jsonFilePath, $valuesToReplace);

        return self::decodeJsonAsArray($jsonFilePath, $fileContents);
    }


    /**
     * @param array<string, string> $valuesToReplace
     */
    public static function loadObjectFromJsonFileAndReplace(string $jsonFilePath, array $valuesToReplace): stdClass
    {
        $fileContents = self::loadJsonStringFromJsonFileAndReplace($jsonFilePath, $valuesToReplace);

        return self::decodeJsonAsObject($jsonFilePath, $fileContents);
    }


    /**
     * @param array<string, int|float|bool|string|null> $valuesToReplace
     */
    public static function loadJsonStringFromJsonFileAndReplace(string $jsonFilePath, array $valuesToReplace): string
    {
        $fileContents = self::loadAsString($jsonFilePath);

        return JsonValuesReplacer::replace($valuesToReplace, $fileContents);
    }


    public static function loadAsString(string $filePath): string
    {
        try {
            return FileSystem::read($filePath);
        } catch (IOException $exception) {
            throw new LogicException('Cannot load file ' . $filePath . ': ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }


    /**
     * @return mixed[]|stdClass
     */
    private static function decodeJson(string $jsonFilePath, string $fileContents, bool $asArray): mixed
    {
        try {
            return Json::decode($fileContents, $asArray ? Json::FORCE_ARRAY : 0);
        } catch (JsonException $exception) {
            throw new LogicException('File ' . $jsonFilePath . ' is not JSON: ' . $exception->getMessage(), $exception->getCode(), $exception);
        }
    }


    /**
     * @return mixed[]
     */
    private static function decodeJsonAsArray(string $jsonFilePath, string $fileContents): array
    {
        $array = self::decodeJson($jsonFilePath, $fileContents, true);
        assert(is_array($array));

        return $array;
    }


    private static function decodeJsonAsObject(string $jsonFilePath, string $fileContents): stdClass
    {
        $object = self::decodeJson($jsonFilePath, $fileContents, false);
        assert(is_object($object));

        return $object;
    }
}
