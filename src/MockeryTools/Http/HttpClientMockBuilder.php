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
     * @param mixed[] $expectedResponseBody
     * @param mixed[] $requestBody
     *
     * @throws JsonException
     */
    public function expectRequest(
        string $httpMethod,
        string $endpoint,
        array $expectedResponseBody = [],
        array $requestBody = []
    ): self {
        $responseBody = Json::encode($expectedResponseBody);

        $this->httpClientMock->shouldReceive('request')
            ->with(
                $httpMethod,
                $this->createRequestUrl($endpoint),
                $this->createRequestOptions($httpMethod, $requestBody)
            )
            ->once()
            ->andReturn(new Response(200, [], $responseBody));

        return $this;
    }


    /**
     * @param mixed[] $expectedResponseBody
     * @param mixed[] $requestBody
     *
     * @throws JsonException
     */
    public function expectFailedRequest(
        string $httpMethod,
        string $endpoint,
        array $expectedResponseBody = [],
        array $requestBody = [],
        int $errorCode = 400
    ): self {
        $request = new Request(
            $httpMethod,
            $this->createRequestUrl($endpoint),
            $this->expectedHeaders,
            Json::encode($requestBody)
        );
        $response = new Response($errorCode, [], Json::encode($expectedResponseBody));

        $requestException = RequestException::create($request, $response);

        $this->httpClientMock->shouldReceive('request')
            ->with(
                $httpMethod,
                $this->createRequestUrl($endpoint),
                $this->createRequestOptions($httpMethod, $requestBody)
            )
            ->once()
            ->andThrow($requestException);

        return $this;
    }


    /**
     * @param mixed[] $requestBody
     *
     * @return mixed[]
     */
    private function createRequestOptions(string $httpMethod, array $requestBody = []): array
    {
        $requestOptions = [RequestOptions::HEADERS => $this->expectedHeaders];

        if (Strings::upper($httpMethod) !== 'GET') {
            $requestOptions[RequestOptions::JSON] = $requestBody;
        }

        return $requestOptions;
    }


    private function createRequestUrl(string $endpoint): string
    {
        return $this->basePath . $endpoint;
    }
}
