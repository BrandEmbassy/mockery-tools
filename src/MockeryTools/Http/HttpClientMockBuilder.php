<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;
use function array_merge_recursive;

/**
 * @final
 */
class HttpClientMockBuilder
{
    use MockeryPHPUnitIntegration;

    private ClientInterface&MockInterface $httpClientMock;

    private string $basePath;

    /**
     * @var array<string, string>
     */
    private array $expectedHeaders;


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
     * @param array<string, mixed>|stdClass|null $expectedRequestData
     * @param array<string, mixed> $expectedRequestOptions
     *
     * @throws JsonException
     */
    public function expectRequest(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array|stdClass|null $expectedRequestData = null,
        int $statusCodeToReturn = 200,
        array $expectedRequestOptions = []
    ): self {
        $responseBody = Json::encode($responseDataToReturn);
        $this->httpClientMock->expects('request')
            ->with(
                $expectedHttpMethod,
                $this->createRequestUrl($expectedEndpoint),
                HttpRequestOptionsMatcher::create(
                    $this->mergeExpectedHeadersWithRequestOptions($expectedRequestOptions),
                    $expectedRequestData,
                ),
            )
            ->andReturn(new Response($statusCodeToReturn, [], $responseBody));

        return $this;
    }


    /**
     * @param mixed[] $responseDataToReturn
     * @param mixed[]|stdClass|null $expectedRequestData
     * @param array<string, mixed> $expectedRequestOptions
     *
     * @throws JsonException
     */
    public function expectFailedRequest(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array|stdClass|null $expectedRequestData = null,
        int $errorCodeToReturn = 400,
        array $expectedRequestOptions = []
    ): self {
        $request = new Request(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            Json::encode($expectedRequestData),
        );
        $response = new Response($errorCodeToReturn, [], Json::encode($responseDataToReturn));
        $exceptionToThrow = RequestException::create($request, $response);

        $this->httpClientMock->expects('request')
            ->with(
                $expectedHttpMethod,
                $this->createRequestUrl($expectedEndpoint),
                HttpRequestOptionsMatcher::create(
                    $this->mergeExpectedHeadersWithRequestOptions($expectedRequestOptions),
                    $expectedRequestData,
                ),
            )
            ->andThrow($exceptionToThrow);

        return $this;
    }


    /**
     * @param array<string, mixed> $responseDataToReturn
     * @param array<string, mixed>|stdClass|null $expectedRequestData
     * @param array<string, mixed> $options
     *
     * @throws JsonException
     */
    public function expectSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseDataToReturn = [],
        array|stdClass|null $expectedRequestData = null,
        int $statusCodeToReturn = 200,
        ?array $options = null,
    ): self {
        $responseBody = Json::encode($responseDataToReturn);
        $expectedRequestBody = $expectedRequestData !== null ? Json::encode($expectedRequestData) : '';
        $requestMatcher = $this->createRequestMatcher($expectedHttpMethod, $expectedEndpoint, $expectedRequestBody);

        $this->httpClientMock->expects('send')
            ->with(...($options === null ? [$requestMatcher] : [$requestMatcher, $options]))
            ->andReturn(new Response($statusCodeToReturn, [], $responseBody));

        return $this;
    }


    /**
     * @param array<string, mixed> $responseDataToReturn
     * @param array<string, mixed>|stdClass|null $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectFailedSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array|stdClass|null $expectedRequestData = null,
        int $errorCodeToReturn = 400,
        array $responseDataToReturn = []
    ): self {
        $expectedRequestBody = $expectedRequestData !== null ? Json::encode($expectedRequestData) : '';
        $request = new Request(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            $expectedRequestBody,
        );
        $response = new Response($errorCodeToReturn, [], Json::encode($responseDataToReturn));
        $requestMatcher = $this->createRequestMatcher($expectedHttpMethod, $expectedEndpoint, $expectedRequestBody);
        $exceptionToThrow = RequestException::create($request, $response);

        $this->httpClientMock->expects('send')
            ->with($requestMatcher)
            ->andThrows($exceptionToThrow);

        return $this;
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
            $expectedRequestBody,
        );
    }


    /**
     * @param array<string, mixed> $expectedRequestOptions
     *
     * @return array<string, mixed>
     */
    private function mergeExpectedHeadersWithRequestOptions(array $expectedRequestOptions): array
    {
        return array_merge_recursive(
            [RequestOptions::HEADERS => $this->expectedHeaders],
            $expectedRequestOptions,
        );
    }
}
