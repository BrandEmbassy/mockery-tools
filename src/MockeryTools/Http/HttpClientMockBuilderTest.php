<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use BrandEmbassy\MockeryTools\Exception\ExceptionAssertions;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Throwable;
use function assert;

final class HttpClientMockBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const BASE_PATH = 'https://api.com/v2';
    private const HEADERS = ['Authorization' => 'Bearer thisIsSomeToken'];


    /**
     * @throws Throwable
     */
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


    /**
     * @throws Throwable
     */
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


    /**
     * @throws Throwable
     */
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


    /**
     * @throws Throwable
     */
    public function testSendWithSuccess(): void
    {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectSend('GET', '/users/25', ['name' => 'Prokop Buben'], ['id' => 25])
            ->build();

        $request = new Request(
            'GET',
            new Uri('https://api.com/v2/users/25'),
            self::HEADERS,
            '{"id":25}'
        );
        $response = $httpClientMock->send($request);

        Assert::assertSame('{"name":"Prokop Buben"}', (string)$response->getBody());
    }


    /**
     * @dataProvider requestExceptionProvider
     *
     * @param class-string<RequestException> $exceptionToThrowClassname
     *
     * @throws Throwable
     */
    public function testRequestExceptionIsThrownOnSend(
        string $exceptionToThrowClassname
    ): void {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectFailedSend(
                'POST',
                '/users',
                $exceptionToThrowClassname,
                ['name' => 'Prokop Buben'],
                401,
                ['error' => 'You shall not pass!']
            )
            ->build();

        $request = new Request(
            'POST',
            new Uri('https://api.com/v2/users'),
            self::HEADERS,
            '{"name":"Prokop Buben"}'
        );

        ExceptionAssertions::assertExceptionCallback(
            $exceptionToThrowClassname,
            static function (RequestException $exception): void {
                $response = $exception->getResponse();
                assert($response !== null);
                Assert::assertSame(401, $response->getStatusCode());
                Assert::assertSame('{"error":"You shall not pass!"}', (string)$response->getBody());
            },
            static function () use ($httpClientMock, $request): void {
                $httpClientMock->send($request);
            }
        );
    }


    /**
     * @return array<string, array<string, class-string>>
     */
    public function requestExceptionProvider(): array
    {
        return [
            'Bad response' => [
                'exceptionToThrowClassname' => BadResponseException::class,
            ],
            'Client failure' => [
                'exceptionToThrowClassname' => ClientException::class,
            ],
            'Server failure' => [
                'exceptionToThrowClassname' => ServerException::class,
            ],
        ];
    }


    /**
     * @dataProvider someGeneralGuzzleExceptionProvider
     *
     * @param class-string<Throwable> $exceptionToThrowClassname
     *
     * @throws Throwable
     */
    public function testSomeGeneralGuzzleExceptionIsThrownOnSend(
        string $exceptionToThrowClassname
    ): void {
        $httpClientMock = HttpClientMockBuilder::create(self::BASE_PATH, self::HEADERS)
            ->expectFailedSend(
                'POST',
                '/users',
                $exceptionToThrowClassname,
                ['name' => 'Prokop Buben'],
                401,
                ['error' => 'You shall not pass!']
            )
            ->build();

        $request = new Request(
            'POST',
            new Uri('https://api.com/v2/users'),
            self::HEADERS,
            '{"name":"Prokop Buben"}'
        );

        $this->expectException($exceptionToThrowClassname);

        $httpClientMock->send($request);
    }


    /**
     * @return array<string, array<string, class-string>>
     */
    public function someGeneralGuzzleExceptionProvider(): array
    {
        return [
            'Transfer failure' => [
                'exceptionToThrowClassname' => TransferException::class,
            ],
            'Connection failure' => [
                'exceptionToThrowClassname' => ConnectException::class,
            ],
            'Invalid argument' => [
                'exceptionToThrowClassname' => InvalidArgumentException::class,
            ],
        ];
    }
}
