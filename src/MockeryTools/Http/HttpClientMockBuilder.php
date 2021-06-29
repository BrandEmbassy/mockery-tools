<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\Matcher\Closure;
use Mockery\MockInterface;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Psr\Http\Message\RequestInterface;
use function assert;
use function is_a;

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
     * @param array<string, mixed> $expectedResponseBody
     * @param array<string, mixed> $requestBody
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
     * @param array<string, mixed> $responseData
     * @param array<string, mixed> $expectedRequestData
     *
     * @throws JsonException
     */
    public function expectSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        array $responseData = [],
        array $expectedRequestData = []
    ): self {
        $responseBody = Json::encode($responseData);
        $expectedRequestBody = Json::encode($expectedRequestData);
        $requestMatcher = $this->createRequestMatcher(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $expectedRequestBody
        );

        $this->httpClientMock->shouldReceive('send')
            ->with($requestMatcher)
            ->once()
            ->andReturn(new Response(200, [], $responseBody));

        return $this;
    }


    /**
     * @param array<string, mixed> $responseData
     * @param array<string, mixed> $expectedRequestData
     * @param class-string $guzzleExceptionClassname
     *
     * @throws JsonException
     */
    public function expectFailedSend(
        string $expectedHttpMethod,
        string $expectedEndpoint,
        string $guzzleExceptionClassname,
        array $expectedRequestData = [],
        int $errorCode = 400,
        array $responseData = []
    ): self {
        assert(is_a($guzzleExceptionClassname, GuzzleException::class, true));
        $expectedRequestBody = Json::encode($expectedRequestData);
        $request = new Request(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $this->expectedHeaders,
            $expectedRequestBody
        );
        $response = new Response($errorCode, [], Json::encode($responseData));
        $exceptionToThrow = $this->getExceptionToThrow($guzzleExceptionClassname, $request, $response, $errorCode);
        $requestMatcher = $this->createRequestMatcher(
            $expectedHttpMethod,
            $this->createRequestUrl($expectedEndpoint),
            $expectedRequestBody
        );

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


    private function containsExpectedHeaders(RequestInterface $request): bool
    {
        $requestHeaders = $request->getHeaders();
        foreach ($this->expectedHeaders as $headerName => $headerValue) {
            if (!isset($requestHeaders[$headerName])) {
                return false;
            }
            if ($requestHeaders[$headerName][0] !== $headerValue) {
                return false;
            }
        }

        return true;
    }


    private function createRequestMatcher(
        string $expectedHttpMethod,
        string $expectedUri,
        string $expectedRequestBody
    ): Closure {
        return Mockery::on(
            function (
                RequestInterface $request
            ) use (
                $expectedHttpMethod,
                $expectedUri,
                $expectedRequestBody
            ): bool {
                return $request->getMethod() === $expectedHttpMethod
                    && (string)$request->getUri() === $expectedUri
                    && (string)$request->getBody() === $expectedRequestBody
                    && $this->containsExpectedHeaders($request);
            }
        );
    }


    /**
     * @param class-string<GuzzleException> $guzzleExceptionClassname
     */
    private function getExceptionToThrow(
        string $guzzleExceptionClassname,
        Request $request,
        Response $response,
        int $errorCode
    ): GuzzleException {
        if (is_a($guzzleExceptionClassname, RequestException::class, true)) {
            return new $guzzleExceptionClassname('Request call failure', $request, $response);
        }

        if (is_a($guzzleExceptionClassname, ConnectException::class, true)) {
            return new $guzzleExceptionClassname('Request call failure', $request);
        }

        return new $guzzleExceptionClassname('Request call failure', $errorCode);
    }
}
