<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http\Exchange;

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use Psr\Http\Message\RequestInterface;
use function array_merge;
use function assert;
use function http_build_query;
use function is_array;

/**
 * @phpstan-type TRequestOptions = array{
 *     headers?: array<string, string>,
 *     json?: mixed,
 *     body?: string,
 *     form_params?: array<string, string>,
 *     multipart?: mixed,
 *     version?: string,
 * }
 * @final
 */
class TestOnlyExpectedExchangeFactory
{
    private const VERSION = '1.1';

    private string $userAgent;

    private string $integrationId;

    private string $cxoneServiceIdentifier;

    private string $fakeUuid;


    public function __construct(string $userAgent, string $integrationId, string $cxoneServiceIdentifier, string $fakeUuid)
    {
        $this->userAgent = $userAgent;
        $this->integrationId = $integrationId;
        $this->cxoneServiceIdentifier = $cxoneServiceIdentifier;
        $this->fakeUuid = $fakeUuid;
    }


    /**
     * @param TRequestOptions $requestOptions
     */
    public function create(
        string $method,
        string $url,
        int $responseStatusCode,
        string $responseBody = '',
        string $responseContentType = '',
        array $requestOptions = []
    ): TestOnlyExpectedExchange {
        $request = $this->createRequest($method, $url, $requestOptions);

        $responseHeaders = [];
        if ($responseContentType !== '') {
            $responseHeaders['Content-Type'] = $responseContentType;
        }

        $response = new Response($responseStatusCode, $responseHeaders, $responseBody);

        return new TestOnlyExpectedExchange($request, $response);
    }


    /**
     * @param TRequestOptions $requestOptions
     */
    private function createRequest(
        string $method,
        string $url,
        array $requestOptions = []
    ): RequestInterface {
        if (isset($requestOptions[RequestOptions::JSON])) {
            $requestOptions[RequestOptions::BODY] = Utils::jsonEncode($requestOptions[RequestOptions::JSON]);
            $requestOptions[RequestOptions::HEADERS]['Content-Type'] = 'application/json';

            unset($requestOptions[RequestOptions::JSON]);
        }

        if (isset($requestOptions[RequestOptions::FORM_PARAMS])) {
            $requestOptions[RequestOptions::BODY] = http_build_query($requestOptions[RequestOptions::FORM_PARAMS], '', '&');
            $requestOptions[RequestOptions::HEADERS]['Content-Type'] = 'application/x-www-form-urlencoded';

            unset($requestOptions[RequestOptions::FORM_PARAMS]);
        }

        if (isset($requestOptions[RequestOptions::MULTIPART])) {
            $multipart = $requestOptions[RequestOptions::MULTIPART];
            assert(is_array($multipart));
            $requestOptions[RequestOptions::BODY] = new MultipartStream($multipart);

            unset($requestOptions[RequestOptions::MULTIPART]);
        }

        $defaultHeaders = $this->getDefaultHeaders();
        $headers = $requestOptions[RequestOptions::HEADERS] ?? [];
        $headers = array_merge($headers, $defaultHeaders);
        $body = $requestOptions[RequestOptions::BODY] ?? null;
        $version = $requestOptions[RequestOptions::VERSION] ?? self::VERSION;

        $request = new Request(
            $method,
            $url,
            $headers,
            $body,
            $version,
        );

        $requestBodySize = $request->getBody()->getSize();
        if ($requestBodySize !== null && $requestBodySize > 0 && !isset($headers['Content-Length'])) {
            $request = $request->withHeader('Content-Length', (string)$requestBodySize);
        }

        if ($request->getBody() instanceof MultipartStream) {
            $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary='
                . $request->getBody()->getBoundary());
        }

        return $request;
    }


    /**
     * @return mixed[]
     */
    private function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => $this->userAgent,
            'X-Caller-Service-ID' => $this->integrationId,
            'Immediate-Service-Identifier' => $this->cxoneServiceIdentifier,
            'Originating-Service-Identifier' => $this->cxoneServiceIdentifier,
            'X-Trace-ID' => $this->fakeUuid,
            'X-Transaction-ID' => $this->fakeUuid,
        ];
    }
}
