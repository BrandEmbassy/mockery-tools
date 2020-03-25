<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class HttpClientMockBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const BASE_PATH = 'https://api.com/v2';
    private const HEADERS = ['Authorization' => 'Bearer thisIsSomeToken'];


    public function testExpectedTwoRequests(): void
    {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectRequest('POST', '/users', ['id' => 25], ['name' => 'Prokop Buben'])
            ->expectRequest('GET', '/users/25', ['id' => 25, 'name' => 'Prokop Buben'])
            ->build();

        $postResponse = $httpClientMock->request(
            'POST',
            'https://api.com/v2/users',
            [RequestOptions::HEADERS => self::HEADERS, RequestOptions::JSON => ['name' => 'Prokop Buben']]
        );

        $getResponse = $httpClientMock->request(
            'GET',
            'https://api.com/v2/users/25',
            [RequestOptions::HEADERS => self::HEADERS]
        );

        Assert::assertSame('{"id":25}', (string)$postResponse->getBody());
        Assert::assertSame('{"id":25,"name":"Prokop Buben"}', (string)$getResponse->getBody());
    }


    public function testClientExceptionIsThrown(): void
    {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectFailedRequest('GET', '/users/25', ['error' => 'User not found'], [], 404)
            ->build();

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage(
            'Client error: `GET https://api.com/v2/users/25` resulted in a `404 Not Found` response:
{"error":"User not found"}'
        );

        $httpClientMock->request(
            'GET',
            'https://api.com/v2/users/25',
            [RequestOptions::HEADERS => self::HEADERS]
        );
    }


    public function testServerExceptionIsThrown(): void
    {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectFailedRequest('GET', '/users/25', ['error' => 'Internal server error'], [], 500)
            ->build();

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage(
            'Server error: `GET https://api.com/v2/users/25` resulted in a `500 Internal Server Error` response:
{"error":"Internal server error"}'
        );

        $httpClientMock->request(
            'GET',
            'https://api.com/v2/users/25',
            [RequestOptions::HEADERS => self::HEADERS]
        );
    }
}
