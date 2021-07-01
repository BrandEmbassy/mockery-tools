<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use Mockery\Matcher\MatcherAbstract;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use function assert;
use function is_array;

final class HttpRequestOptionsMatcher extends MatcherAbstract
{
    /**
     * @var array<string, string>
     */
    private $expectedHeaders;

    /**
     * @var mixed[]|null
     */
    private $expectedRequestData;


    /**
     * @param array<string, string> $expectedHeaders
     * @param mixed[]|null $expectedRequestData
     */
    public function __construct(array $expectedHeaders, ?array $expectedRequestData = null)
    {
        parent::__construct();

        $this->expectedHeaders = $expectedHeaders;
        $this->expectedRequestData = $expectedRequestData;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert(is_array($actual));

        foreach ($this->expectedHeaders as $headerName => $headerValue) {
            if (!isset($actual[RequestOptions::HEADERS][$headerName])
                || $actual[RequestOptions::HEADERS][$headerName] !== $headerValue
            ) {
                return false;
            }
        }

        if ($this->expectedRequestData === null) {
            return true;
        }

        try {
            $expectedRequestDataAsJson = Json::encode($this->expectedRequestData);
            $givenRequestDataAsJson = Json::encode($actual[RequestOptions::JSON] ?? '{}');
        } catch (JsonException $exception) {
            return false;
        }

        return $expectedRequestDataAsJson === $givenRequestDataAsJson;
    }


    public function __toString(): string
    {
        return '<HttpRequestOptions>';
    }
}
