<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools;

use LogicException;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

final class FileLoader
{
    /**
     * @return mixed[]
     */
    public static function loadArrayFromJsonFile(string $filePath): array
    {
        try {
            return Json::decode(self::loadAsString($filePath), Json::FORCE_ARRAY);
        } catch (JsonException $exception) {
            throw new LogicException('File ' . $filePath . ' is not valid JSON');
        }
    }


    public static function loadAsString(string $filePath): string
    {
        return FileSystem::read($filePath);
    }
}
