<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;

/**
 * @final
 */
class ResponseBodyParser
{
    public static function parseAsString(ResponseInterface $response): string
    {
        return (string)$response->getBody();
    }


    /**
     * @return mixed[]
     */
    public static function parseAsArray(ResponseInterface $response): array
    {
        return Json::decode(self::parseAsString($response), Json::FORCE_ARRAY);
    }
}
