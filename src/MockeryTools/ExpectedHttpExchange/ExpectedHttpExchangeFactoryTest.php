<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\ExpectedHttpExchange;

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\RequestOptions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ExpectedHttpExchangeFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const USER_AGENT = 'TestUserAgent';

    private const INTEGRATION_ID = 'test-integration';

    private const CXONE_SERVICE_IDENTIFIER = 'test-service';

    private const X_TRACE_ID = 'test-trace-id';

    private const X_TRANSACTION_ID = 'test-transaction-id';

    private ExpectedHttpExchangeFactory $factory;


    protected function setUp(): void
    {
        $this->factory = new ExpectedHttpExchangeFactory(
            self::USER_AGENT,
            self::INTEGRATION_ID,
            self::CXONE_SERVICE_IDENTIFIER,
            self::X_TRACE_ID,
            self::X_TRANSACTION_ID,
        );
    }


    public function testCreateExchangeWithBasicRequest(): void
    {
        $method = 'GET';
        $url = 'https://api.example.com/users';
        $statusCode = 200;
        $responseBody = '{"id": 1, "name": "John Doe"}';
        $responseContentType = 'application/json';

        $exchange = $this->factory->createExchange(
            $method,
            $url,
            $statusCode,
            $responseBody,
            $responseContentType,
        );

        $request = $exchange->getRequest();
        Assert::assertSame($method, $request->getMethod());
        Assert::assertSame($url, (string)$request->getUri());

        Assert::assertSame(self::USER_AGENT, $request->getHeaderLine('User-Agent'));
        Assert::assertSame(self::INTEGRATION_ID, $request->getHeaderLine('X-Caller-Service-ID'));
        Assert::assertSame(self::CXONE_SERVICE_IDENTIFIER, $request->getHeaderLine('Immediate-Service-Identifier'));
        Assert::assertSame(self::CXONE_SERVICE_IDENTIFIER, $request->getHeaderLine('Originating-Service-Identifier'));
        Assert::assertSame(self::X_TRACE_ID, $request->getHeaderLine('X-Trace-ID'));
        Assert::assertSame(self::X_TRANSACTION_ID, $request->getHeaderLine('X-Transaction-ID'));

        $response = $exchange->getResponse();
        Assert::assertSame($statusCode, $response->getStatusCode());
        Assert::assertSame($responseBody, (string)$response->getBody());
        Assert::assertSame($responseContentType, $response->getHeaderLine('Content-Type'));
    }


    /**
     * @param array<string, mixed> $requestOptions
     */
    #[DataProvider('requestOptionsProvider')]
    public function testCreateExchangeWithRequestOptions(
        array $requestOptions,
        callable $assertionCallable
    ): void {
        $method = 'POST';
        $url = 'https://api.example.com/endpoint';
        $statusCode = 201;

        $exchange = $this->factory->createExchange(
            $method,
            $url,
            $statusCode,
            '',
            '',
            $requestOptions,
        );

        $assertionCallable($exchange->getRequest());
    }


    /**
     * @return array<string, array{requestOptions: array<string, mixed>, assertionCallable: callable}>
     */
    public static function requestOptionsProvider(): array
    {
        return [
            'JSON request' => [
                'requestOptions' => [
                    RequestOptions::JSON => [
                        'name' => 'John',
                        'email' => 'john@example.com',
                    ],
                ],
                'assertionCallable' => function (RequestInterface $request): void {
                    Assert::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                    Assert::assertSame('{"name":"John","email":"john@example.com"}', (string)$request->getBody());
                },
            ],
            'Form params request' => [
                'requestOptions' => [
                    RequestOptions::FORM_PARAMS => [
                        'name' => 'John',
                        'email' => 'john@example.com',
                    ],
                ],
                'assertionCallable' => function (RequestInterface $request): void {
                    Assert::assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                    Assert::assertSame('name=John&email=john%40example.com', (string)$request->getBody());
                },
            ],
            'Multipart request' => [
                'requestOptions' => [
                    RequestOptions::MULTIPART => [
                        [
                            'name' => 'field_name',
                            'contents' => 'field_content',
                        ],
                        [
                            'name' => 'file',
                            'contents' => 'file_content',
                            'filename' => 'test.txt',
                        ],
                    ],
                ],
                'assertionCallable' => function (RequestInterface $request): void {
                    Assert::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
                    Assert::assertInstanceOf(MultipartStream::class, $request->getBody());
                },
            ],
            'Custom headers request' => [
                'requestOptions' => [
                    RequestOptions::HEADERS => [
                        'Authorization' => 'Bearer token123',
                        'Accept' => 'application/json',
                        'X-Custom-Header' => 'custom-value',
                    ],
                ],
                'assertionCallable' => function (RequestInterface $request): void {
                    Assert::assertSame('Bearer token123', $request->getHeaderLine('Authorization'));
                    Assert::assertSame('application/json', $request->getHeaderLine('Accept'));
                    Assert::assertSame('custom-value', $request->getHeaderLine('X-Custom-Header'));
                    Assert::assertSame(self::USER_AGENT, $request->getHeaderLine('User-Agent'));
                    Assert::assertSame(self::INTEGRATION_ID, $request->getHeaderLine('X-Caller-Service-ID'));
                },
            ],
        ];
    }


    public function testCreateFailedRequest(): void
    {
        $method = 'GET';
        $url = 'https://api.example.com/users';

        $clientExceptionMock = $this->createMock(ClientExceptionInterface::class);

        $failedExchange = $this->factory->createFailedRequest(
            $method,
            $url,
            $clientExceptionMock,
        );

        Assert::assertSame($method, $failedExchange->getRequest()->getMethod());
        Assert::assertSame($url, (string)$failedExchange->getRequest()->getUri());
        Assert::assertSame($clientExceptionMock, $failedExchange->getException());
    }


    public function testCreateExchangeWithResponseHeaders(): void
    {
        $method = 'GET';
        $url = 'https://api.example.com/users';
        $statusCode = 200;
        $responseBody = '{"data": "test"}';

        $responseHeaders = [
            'X-Rate-Limit-Remaining' => '98',
            'X-Rate-Limit-Limit' => '100',
        ];
        $exchange = $this->factory->createExchange(
            $method,
            $url,
            $statusCode,
            $responseBody,
            'application/json',
            [],
            $responseHeaders,
        );

        $response = $exchange->getResponse();

        foreach ([
            'X-Rate-Limit-Remaining' => '98',
            'X-Rate-Limit-Limit' => '100',
            'Content-Type' => 'application/json',
        ] as $header => $value) {
            Assert::assertSame($value, $response->getHeaderLine($header));
        }
    }
}
