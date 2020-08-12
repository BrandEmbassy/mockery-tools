<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

final class ResponseAssertions
{
    public static function assertJsonResponseEqualsJsonFile(string $jsonFilePath, ResponseInterface $response): void
    {
        Assert::assertJsonStringEqualsJsonFile($jsonFilePath, self::getResponseBody($response));
    }


    public static function assertJsonResponseEqualsJsonString(string $expectedJson, ResponseInterface $response): void
    {
        Assert::assertJsonStringEqualsJsonString($expectedJson, self::getResponseBody($response));
    }


    public static function assertJsonResponseEqualsArray(array $expectedArray, ResponseInterface $response): void
    {
        $expectedJson = Json::encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expectedJson, self::getResponseBody($response));
    }


    public static function assertResponseHeaders(array $expectedHeaders, ResponseInterface $response): void
    {
        foreach ($expectedHeaders as $headerName => $headerValue) {
            self::assertResponseHeader($headerValue, $headerName, $response);
        }
    }


    public static function assertResponseHeader(
        string $expectedHeaderValue,
        string $headerName,
        ResponseInterface $response
    ): void {
        Assert::assertSame($expectedHeaderValue, $response->getHeaderLine($headerName));
    }


    public static function assertResponseStatusCode(int $expectedStatusCode, ResponseInterface $response): void
    {
        Assert::assertSame($expectedStatusCode, $response->getStatusCode());
    }


    private static function getResponseBody(ResponseInterface $response): string
    {
        return (string)$response->getBody();
    }
}
