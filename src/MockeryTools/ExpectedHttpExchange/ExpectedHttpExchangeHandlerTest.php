<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\ExpectedHttpExchange;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Exception\NoMatchingExpectationException;
use Mockery\MockInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ExpectedHttpExchangeHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const URL = 'https://api.example.com/users';

    private ExpectedHttpExchangeHandler $handler;

    private RequestDiffComputerInterface&MockInterface $requestDiffComputer;


    protected function setUp(): void
    {
        $this->requestDiffComputer = Mockery::mock(RequestDiffComputerInterface::class);
        $this->handler = new ExpectedHttpExchangeHandler($this->requestDiffComputer);
    }


    public function testHandlerShouldReturnExpectedResponse(): void
    {
        $exchange = $this->createJsonExchange('GET', self::URL, 200, '{"id": 1}');
        $this->handler->expectExchange($exchange);

        $actualRequest = $this->createJsonRequest('GET', self::URL);

        $handler = $this->handler->__invoke();
        $promise = $handler($actualRequest);

        Assert::assertInstanceOf(FulfilledPromise::class, $promise);
        Assert::assertSame($exchange->getResponse(), $promise->wait());
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldThrowExceptionForUnexpectedRequest(): void
    {
        $exchange = $this->createJsonExchange('GET', self::URL, 200, '{"id": 1}');
        $this->handler->expectExchange($exchange);

        $differentRequest = $this->createJsonRequest('POST', self::URL, '{"name": "Test"}');

        $this->requestDiffComputer->expects('outputRequestDiffs')
            ->with($differentRequest, [$exchange]);

        $handler = $this->handler->__invoke();

        $this->expectException(NoMatchingExpectationException::class);
        $handler($differentRequest);
    }


    public function testHandlerShouldReturnRejectedPromiseForFailedHttpExchange(): void
    {
        $mockException = $this->createMock(ClientExceptionInterface::class);
        $failedExchange = $this->createFailedExchange('GET', self::URL, $mockException);

        $this->handler->expectExchange($failedExchange);

        $actualRequest = $this->createJsonRequest('GET', self::URL);

        $handler = $this->handler->__invoke()($actualRequest);
        $promise = $handler;

        Assert::assertInstanceOf(RejectedPromise::class, $promise);

        try {
            $promise->wait();
            Assert::fail('Expected exception was not thrown');
        } catch (ClientExceptionInterface $e) {
            Assert::assertSame($mockException, $e);
        }

        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldReturnRejectedPromiseForResponseWithErrorStatusCode(): void
    {
        $exchange = $this->createJsonExchange('GET', self::URL, 404, '{"error": "Not found"}');
        $this->handler->expectExchange($exchange);

        $actualRequest = $this->createJsonRequest('GET', self::URL);

        $handler = $this->handler->__invoke();
        $promise = $handler($actualRequest);

        Assert::assertInstanceOf(RejectedPromise::class, $promise);
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldMatchJsonRequestsWithDifferentOrder(): void
    {
        $expectedRequest = $this->createJsonRequest('POST', self::URL, '{"name":"John","age":30}');
        $expectedResponse = $this->createJsonResponse(201, '{"id": 1}');
        $exchange = new ExpectedHttpExchange($expectedRequest, $expectedResponse);

        $this->handler->expectExchange($exchange);

        $actualRequest = $this->createJsonRequest('POST', self::URL, '{"age":30,"name":"John"}');

        $handler = $this->handler->__invoke();
        $promise = $handler($actualRequest);

        Assert::assertInstanceOf(FulfilledPromise::class, $promise);
        Assert::assertSame($expectedResponse, $promise->wait());
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldMatchFormRequests(): void
    {
        $expectedRequest = $this->createFormRequest('POST', self::URL, 'name=John&email=john%40example.com');
        $expectedResponse = $this->createJsonResponse(201, '{"success": true}');
        $exchange = new ExpectedHttpExchange($expectedRequest, $expectedResponse);

        $this->handler->expectExchange($exchange);

        $actualRequest = $this->createFormRequest('POST', self::URL, 'email=john%40example.com&name=John');

        $handler = $this->handler->__invoke();
        $promise = $handler($actualRequest);

        Assert::assertInstanceOf(FulfilledPromise::class, $promise);
        Assert::assertSame($expectedResponse, $promise->wait());
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldMatchMultipartRequests(): void
    {
        $multipartData = $this->getMultipartTestData();

        $exchange = $this->createMultipartExchange('POST', self::URL, $multipartData, 201, '{"success": true}');

        $this->handler->expectExchange($exchange);

        $actualRequest = $this->createMultipartRequest('POST', self::URL, $multipartData);

        $handler = $this->handler->__invoke();
        $promise = $handler($actualRequest);

        Assert::assertInstanceOf(FulfilledPromise::class, $promise);
        Assert::assertSame($exchange->getResponse(), $promise->wait());
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testGetExpectedExchangesShouldReturnAddedExchanges(): void
    {
        $exchange1 = $this->createJsonExchange('GET', self::URL . '/1', 200, '{"id": 1}');
        $exchange2 = $this->createJsonExchange('GET', self::URL . '/2', 200, '{"id": 2}');

        $this->handler->expectExchange($exchange1);
        $this->handler->expectExchange($exchange2);

        $expectedExchanges = $this->handler->getExpectedExchanges();

        Assert::assertCount(2, $expectedExchanges);
        Assert::assertSame($exchange1, $expectedExchanges[0]);
        Assert::assertSame($exchange2, $expectedExchanges[1]);
        Assert::assertTrue($this->handler->hasExpectedExchanges());
    }


    public function testHasExpectedExchangesShouldReturnFalseWhenNoExchangesAdded(): void
    {
        Assert::assertFalse($this->handler->hasExpectedExchanges());
    }


    public function testHandlerShouldThrowExceptionForRequestWithHeadersMismatch(): void
    {
        $expectedRequest = new Request(
            'GET',
            self::URL,
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer test-token',
                'Accept' => 'application/json',
            ],
        );
        $expectedResponse = $this->createJsonResponse(200, '{"id": 1}');
        $exchange = new ExpectedHttpExchange($expectedRequest, $expectedResponse);

        $this->handler->expectExchange($exchange);

        $actualRequest = new Request(
            'GET',
            self::URL,
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer different-token',
                'X-Custom-Header' => 'Custom Value',
            ],
        );

        $this->requestDiffComputer->expects('outputRequestDiffs')
            ->with($actualRequest, [$exchange]);

        $handler = $this->handler->__invoke();

        $this->expectException(NoMatchingExpectationException::class);
        $handler($actualRequest);
    }


    private function createJsonRequest(string $method, string $url, string $body = ''): RequestInterface
    {
        return new Request($method, $url, ['Content-Type' => 'application/json'], $body);
    }


    private function createJsonResponse(int $statusCode, string $body): ResponseInterface
    {
        return new Response($statusCode, ['Content-Type' => 'application/json'], $body);
    }


    private function createJsonExchange(string $method, string $url, int $statusCode, string $responseBody, string $requestBody = ''): ExpectedHttpExchange
    {
        $request = $this->createJsonRequest($method, $url, $requestBody);
        $response = $this->createJsonResponse($statusCode, $responseBody);

        return new ExpectedHttpExchange($request, $response);
    }


    private function createFormRequest(string $method, string $url, string $body): RequestInterface
    {
        return new Request(
            $method,
            $url,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            $body,
        );
    }


    private function createFailedExchange(string $method, string $url, ClientExceptionInterface $exception): ExpectedFailedHttpExchange
    {
        $request = $this->createJsonRequest($method, $url);

        return new ExpectedFailedHttpExchange($request, $exception);
    }


    /**
     * @return array<array<string, mixed>>
     */
    private function getMultipartTestData(): array
    {
        return [
            [
                'name' => 'field_name',
                'contents' => 'field_content',
            ],
            [
                'name' => 'file',
                'contents' => 'file_content',
                'filename' => 'test.txt',
            ],
        ];
    }


    /**
     * @param mixed[] $multipartData
     */
    private function createMultipartRequest(string $method, string $url, array $multipartData): RequestInterface
    {
        $multipart = new MultipartStream($multipartData);

        return new Request(
            $method,
            $url,
            ['Content-Type' => 'multipart/form-data; boundary=' . $multipart->getBoundary()],
            $multipart,
        );
    }


    /**
     * @param mixed[] $multipartData
     */
    private function createMultipartExchange(
        string $method,
        string $url,
        array $multipartData,
        int $statusCode,
        string $responseBody
    ): ExpectedHttpExchange {
        $request = $this->createMultipartRequest($method, $url, $multipartData);
        $response = $this->createJsonResponse($statusCode, $responseBody);

        return new ExpectedHttpExchange($request, $response);
    }
}
