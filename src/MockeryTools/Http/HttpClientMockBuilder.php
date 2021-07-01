<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;

final class HttpClientMockBuilder
{
    /**
     * @var ClientInterface&MockInterface
     */
    private $httpClientMock;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array<string, string>
     */
    private $expectedHeaders;


    /**
     * @param array<string, string> $expectedHeaders
     */
    public function __construct(string $basePath = '', array $expectedHeaders = [])
    {
        $this->httpClientMock = Mockery::mock(ClientInterface::class);
        $this->basePath = $basePath;
        $this->expectedHeaders = $expectedHeaders;
    }


    /**
     * @param array<string, string> $expectedHeaders
     */
    public static function create(string $basePath = '', array $expectedHeaders = []): self
    {
        return new self($basePath, $expectedHeaders);
    }


    /**
     * @return ClientInterface&MockInterface
     */
    public function build(): ClientInterface
    {
        return $this->httpClientMock;
    }


    /**
     * @param array<string, mixed> $responseDataToReturn
     * @param array<string, mixed> $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectRequest(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array $expectedRequestData = []
    ): self {
        $responseBody = Json::encode($responseDataToReturn);

        $this->httpClientMock->shouldReceive('request')
            ->with(
                $expectedHttpMethod,
                $this->createRequestUrl($expectedEndpoint),
                $this->createRequestOptions($expectedHttpMethod, $expectedRequestData)
            )
            ->once()
            ->andReturn(new Response(200, [], $responseBody));

        return $this;
    }


    /**
     * @param mixed[] $responseDataToReturn
     * @param mixed[] $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectFailedRequest(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array $expectedRequestData = [],
        int $errorCodeToReturn = 400
    ): self {
        $request = new Request(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            Json::encode($expectedRequestData)
        );
        $response = new Response($errorCodeToReturn, [], Json::encode($responseDataToReturn));
        $exceptionToThrow = RequestException::create($request, $response);

        $this->httpClientMock->shouldReceive('request')
            ->with(
                $expectedHttpMethod,
                $this->createRequestUrl($expectedEndpoint),
                $this->createRequestOptions($expectedHttpMethod, $expectedRequestData)
            )
            ->once()
            ->andThrow($exceptionToThrow);

        return $this;
    }


    /**
     * @param array<string, mixed> $responseDataToReturn
     * @param array<string, mixed> $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array $expectedRequestData = []
    ): self {
        $responseBody = Json::encode($responseDataToReturn);
        $expectedRequestBody = Json::encode($expectedRequestData);
        $requestMatcher = $this->createRequestMatcher($expectedHttpMethod, $expectedEndpoint, $expectedRequestBody);

        $this->httpClientMock->shouldReceive('send')
            ->with($requestMatcher)
            ->once()
            ->andReturn(new Response(200, [], $responseBody));

        return $this;
    }


    /**
     * @param array<string, mixed> $responseDataToReturn
     * @param array<string, mixed> $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectFailedSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $expectedRequestData = [],
        int $errorCodeToReturn = 400,
        array $responseDataToReturn = []
    ): self {
        $expectedRequestBody = Json::encode($expectedRequestData);
        $request = new Request(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            $expectedRequestBody
        );
        $response = new Response($errorCodeToReturn, [], Json::encode($responseDataToReturn));
        $requestMatcher = $this->createRequestMatcher($expectedHttpMethod, $expectedEndpoint, $expectedRequestBody);
        $exceptionToThrow = RequestException::create($request, $response);

        $this->httpClientMock->shouldReceive('send')
            ->with($requestMatcher)
            ->once()
            ->andThrows($exceptionToThrow);

        return $this;
    }


    /**
     * @param array<string, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function createRequestOptions(string $httpMethod, array $requestData = []): array
    {
        $requestOptions = [RequestOptions::HEADERS => $this->expectedHeaders];

        if (Strings::upper($httpMethod) !== 'GET') {
            $requestOptions[RequestOptions::JSON] = $requestData;
        }

        return $requestOptions;
    }


    private function createRequestUrl(string $endpoint): string
    {
        return $this->basePath . $endpoint;
    }


    private function createRequestMatcher(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        string $expectedRequestBody
    ): HttpRequestMatcher {
        return new HttpRequestMatcher(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            $expectedRequestBody
        );
    }
}
