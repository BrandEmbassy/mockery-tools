<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use BrandEmbassy\MockeryTools\FileLoader;
use BrandEmbassy\MockeryTools\Json\JsonValuesReplacer;
use BrandEmbassy\MockeryTools\Snapshot\SnapshotAssertions;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

final class ResponseAssertions
{
    private const HEADER_LOCATION = 'Location';
    private const STATUS_CODE_200 = 200;


    public static function assertEmptyResponse(
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200
    ): void {
        self::assertResponseBody('', $response, $expectedStatusCode);
    }


    public static function assertResponseBody(
        string $expectedResponseBody,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200
    ): void {
        Assert::assertSame($expectedResponseBody, self::getResponseBody($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    /**
     * @param array<string, mixed> $valuesToReplace
     */
    public static function assertJsonResponseEqualsJsonFile(
        string $jsonFilePath,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200,
        array $valuesToReplace = []
    ): void {
        $expectedJson = FileLoader::loadJsonStringFromJsonFileAndReplace($jsonFilePath, $valuesToReplace);

        Assert::assertJsonStringEqualsJsonString($expectedJson, self::getResponseBody($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    /**
     * @param array<string, mixed> $valuesToReplace
     */
    public static function assertJsonResponseEqualsJsonString(
        string $expectedJson,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200,
        array $valuesToReplace = []
    ): void {
        $expectedJson = JsonValuesReplacer::replace($valuesToReplace, $expectedJson);

        Assert::assertJsonStringEqualsJsonString($expectedJson, self::getResponseBody($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    /**
     * @param mixed[] $expectedArray
     */
    public static function assertJsonResponseEqualsArray(
        array $expectedArray,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200
    ): void {
        $expectedJson = Json::encode($expectedArray);

        Assert::assertJsonStringEqualsJsonString($expectedJson, self::getResponseBody($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    public static function assertHtmlResponseSnapshot(
        string $snapshotFile,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200
    ): void {
        SnapshotAssertions::assertResponseSnapshot($snapshotFile, $response);
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    /**
     * @param array<string, string> $expectedHeaders
     */
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


    public static function assertRedirectResponse(
        string $expectedLocation,
        int $expectedStatusCode,
        ResponseInterface $response
    ): void {
        self::assertResponseHeader(self::HEADER_LOCATION, $expectedLocation, $response);
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    private static function getResponseBody(ResponseInterface $response): string
    {
        return (string)$response->getBody();
    }
}
