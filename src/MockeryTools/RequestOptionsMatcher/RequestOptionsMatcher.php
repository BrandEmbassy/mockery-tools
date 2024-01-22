<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\RequestOptionsMatcher;

use GuzzleHttp\RequestOptions;
use Mockery\Matcher\MatcherInterface;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use function is_array;
use function is_string;
use function ksort;

class RequestOptionsMatcher implements MatcherInterface
{
    /**
     * @var mixed[]
     */
    private array $sortedExpectedRequestOptions;


    /**
     * @param mixed[] $requestOptions
     */
    private function __construct(array $requestOptions)
    {
        if (isset($requestOptions[RequestOptions::BODY]) && is_string($requestOptions[RequestOptions::BODY])) {
            $requestOptions[RequestOptions::BODY] = $this->getSortedBody($requestOptions[RequestOptions::BODY]);
        }
        $this->sortedExpectedRequestOptions = self::sortRequestOptions($requestOptions);
    }


    /**
     * Provided $body will be JSON encoded and added as RequestOption::BODY.
     * Content-Type: application/json header is added
     *
     * @param mixed[] $body
     */
    public static function createWithBody(array $body): self
    {
        $jsonEncodedBody = self::sortAndEncodeBody($body);

        return new self([
            RequestOptions::BODY => $jsonEncodedBody,
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }


    /**
     * Provided $body will be JSON encoded and added as RequestOption::BODY.
     * Content-Type: application/json header is added besided headers provided
     *
     * @param mixed[] $body
     * @param array<string, string> $headers
     */
    public static function createWithBodyAndHeaders(array $body, array $headers): self
    {
        $jsonEncodedBody = self::sortAndEncodeBody($body);

        return new self([
            RequestOptions::BODY => $jsonEncodedBody,
            RequestOptions::HEADERS => $headers + ['Content-Type' => 'application/json'],
        ]);
    }


    /**
     * Provided $stringBody will be set as RequestOption::BODY.
     */
    public static function createWithStringBody(string $stringBody): self
    {
        return new self([RequestOptions::BODY => $stringBody]);
    }


    /**
     * Provided $stringBody will be set as RequestOption::BODY.
     *
     * @param array<string, string> $headers
     */
    public static function createWithStringBodyAndHeaders(string $stringBody, array $headers): self
    {
        return new self([
            RequestOptions::BODY => $stringBody,
            RequestOptions::HEADERS => $headers,
        ]);
    }


    /**
     * Provided $jsonBody will be set as RequestOption::JSON.
     *
     * @param mixed[] $jsonBody
     */
    public static function createWithJsonBody(array $jsonBody): self
    {
        return new self([RequestOptions::JSON => $jsonBody]);
    }


    /**
     * Provided $jsonBody will be set as RequestOption::JSON.
     *
     * @param mixed[] $jsonBody
     * @param array<string, string> $headers
     */
    public static function createWithJsonBodyAndHeaders(array $jsonBody, array $headers): self
    {
        return new self([
            RequestOptions::JSON => $jsonBody,
            RequestOptions::HEADERS => $headers,
        ]);
    }


    public static function createWithEmptyBody(): self
    {
        return new self([]);
    }


    /**
     * @param array<string, string> $headers
     */
    public static function createWithEmptyBodyAndHeaders(array $headers): self
    {
        return new self([RequestOptions::HEADERS => $headers]);
    }


    /**
     * @param mixed[] $requestOptions
     */
    public static function create(array $requestOptions): self
    {
        return new self($requestOptions);
    }


    /**
     * @param mixed[] $requestOptions
     *
     * @internal Do not use in your tests, intended for usage in Channels PseudoIntegration test class only.
     * This method keeps deprecated methods working until they are removed
     */
    public static function createWithConversionFromJsonToBodyOption(array $requestOptions): self
    {
        if (isset($requestOptions[RequestOptions::JSON])) {
            $requestOptions[RequestOptions::BODY] = self::sortAndEncodeBody($requestOptions[RequestOptions::JSON]);
            $requestOptions[RequestOptions::HEADERS]['Content-Type'] = 'application/json';

            unset($requestOptions[RequestOptions::JSON]);
        }

        return new self($requestOptions);
    }


    /**
     * @param mixed[] $body
     */
    private static function sortAndEncodeBody(array $body): string
    {
        return Json::encode(self::sortRequestOptions($body));
    }


    public function withHeader(string $headerName, string $headerValue): self
    {
        $this->sortedExpectedRequestOptions[RequestOptions::HEADERS][$headerName] = $headerValue;

        return new self($this->sortedExpectedRequestOptions);
    }


    /**
     * @param mixed $optionValue
     */
    public function withRequestOption(string $optionName, $optionValue): self
    {
        $this->sortedExpectedRequestOptions[$optionName] = $optionValue;

        return new self($this->sortedExpectedRequestOptions);
    }


    /**
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        if (!is_array($actual)) {
            return false;
        }

        if (isset($actual[RequestOptions::BODY]) && is_string($actual[RequestOptions::BODY])) {
            $actual[RequestOptions::BODY] = $this->getSortedBody($actual[RequestOptions::BODY]);
        }

        $actualSortedRequestOptions = self::sortRequestOptions($actual);

        return $actualSortedRequestOptions === $this->sortedExpectedRequestOptions;
    }


    private function getSortedBody(string $body): string
    {
        try {
            $decodedBody = Json::decode($body, Json::FORCE_ARRAY);

            if (!is_array($decodedBody)) {
                return $body;
            }

            return Json::encode(self::sortRequestOptions($decodedBody));
        } catch (JsonException $exception) {
            return $body;
        }
    }


    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    private static function sortRequestOptions(array $array): array
    {
        ksort($array);
        foreach ($array as &$item) {
            if (is_array($item)) {
                $item = self::sortRequestOptions($item);
            }
        }

        return $array;
    }


    public function __toString()
    {
        return '<RequestOptionsMatcher>';
    }
}
