<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use BrandEmbassy\MockeryTools\Arrays\ArraySubsetAssertions;
use BrandEmbassy\MockeryTools\FileLoader;
use BrandEmbassy\MockeryTools\Json\JsonValuesReplacer;
use BrandEmbassy\MockeryTools\Snapshot\MatchesSnapshots;
use BrandEmbassy\MockeryTools\Snapshot\SnapshotAssertions;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use function sprintf;
use function trigger_error;
use const E_USER_DEPRECATED;

/**
 * @final
 */
class ResponseAssertions
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
        Assert::assertSame($expectedResponseBody, ResponseBodyParser::parseAsString($response));
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

        Assert::assertJsonStringEqualsJsonString($expectedJson, ResponseBodyParser::parseAsString($response));
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

        Assert::assertJsonStringEqualsJsonString($expectedJson, ResponseBodyParser::parseAsString($response));
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

        Assert::assertJsonStringEqualsJsonString($expectedJson, ResponseBodyParser::parseAsString($response));
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }


    public static function assertJsonResponseContainsField(string $fieldName, ResponseInterface $response): void
    {
        $responseBody = ResponseBodyParser::parseAsArray($response);
        Assert::assertArrayHasKey($fieldName, $responseBody);
    }


    /**
     * @param mixed[] $expectedSubset
     */
    public static function assertJsonResponseContainsArraySubset(
        array $expectedSubset,
        ResponseInterface $response
    ): void {
        $responseBody = ResponseBodyParser::parseAsArray($response);
        ArraySubsetAssertions::assertArrayContainsSubset($expectedSubset, $responseBody);
    }


    /**
     * @deprecated
     *
     * @param array<string, string> $valuesToReplace
     */
    public static function assertHtmlResponseSnapshot(
        string $snapshotFile,
        ResponseInterface $response,
        int $expectedStatusCode = self::STATUS_CODE_200,
        array $valuesToReplace = []
    ): void {
        @trigger_error(
            sprintf('Please use %s::assertResponseMatchesHtmlSnapshot instead.', MatchesSnapshots::class),
            E_USER_DEPRECATED,
        );

        SnapshotAssertions::assertResponseSnapshot($snapshotFile, $response, $valuesToReplace);
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
        self::assertResponseHeader($expectedLocation, self::HEADER_LOCATION, $response);
        self::assertResponseStatusCode($expectedStatusCode, $response);
    }
}
