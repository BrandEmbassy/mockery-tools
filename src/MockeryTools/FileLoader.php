<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools;

use BrandEmbassy\MockeryTools\Json\JsonValuesReplacer;
use LogicException;
use Nette\IOException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

final class FileLoader
{
    /**
     * @return mixed[]
     *
     * @throws LogicException
     */
    public static function loadArrayFromJsonFile(string $jsonFilePath): array
    {
        return self::loadArrayFromJsonFileAndReplace($jsonFilePath, []);
    }


    /**
     * @param mixed[] $valuesToReplace
     *
     * @return mixed[]
     *
     * @throws LogicException
     */
    public static function loadArrayFromJsonFileAndReplace(string $jsonFilePath, array $valuesToReplace): array
    {
        $fileContents = self::loadJsonStringFromJsonFileAndReplace($jsonFilePath, $valuesToReplace);

        return self::decodeJson($jsonFilePath, $fileContents);
    }


    /**
     * @param mixed[] $valuesToReplace
     *
     * @throws LogicException
     */
    public static function loadJsonStringFromJsonFileAndReplace(string $jsonFilePath, array $valuesToReplace): string
    {
        $fileContents = self::loadAsString($jsonFilePath);

        return JsonValuesReplacer::replace($valuesToReplace, $fileContents);
    }


    /**
     * @throws LogicException
     */
    public static function loadAsString(string $filePath): string
    {
        try {
            return FileSystem::read($filePath);
        } catch (IOException $exception) {
            throw new LogicException('Cannot load file ' . $filePath . ': ' . $exception->getMessage());
        }
    }


    /**
     * @return mixed[]
     *
     * @throws LogicException
     */
    private static function decodeJson(string $jsonFilePath, string $fileContents): array
    {
        try {
            return Json::decode($fileContents, Json::FORCE_ARRAY);
        } catch (JsonException $exception) {
            throw new LogicException('File ' . $jsonFilePath . ' is not JSON: ' . $exception->getMessage());
        }
    }
}
