<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use Mockery\Matcher\MatcherAbstract;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use function assert;
use function is_array;

/**
 * @final
 */
class HttpRequestOptionsMatcher extends MatcherAbstract
{
    /**
     * @var array<string, mixed>
     */
    private array $expectedRequestOptions;

    /**
     * @var mixed[]|null
     */
    private ?array $expectedRequestData;


    /**
     * @param array<string, string> $expectedHeaders
     * @param mixed[]|null $expectedRequestData
     */
    public function __construct(array $expectedHeaders, ?array $expectedRequestData = null)
    {
        parent::__construct();

        $this->expectedRequestData = $expectedRequestData;
        $this->expectedRequestOptions = [RequestOptions::HEADERS => $expectedHeaders];
    }


    /**
     * @param mixed[]|null $expectedRequestData
     * @param array<string, string> $expectedRequestOptions
     */
    public static function create(array $expectedRequestOptions, ?array $expectedRequestData = null): self
    {
        $self = new self([], $expectedRequestData);
        $self->expectedRequestOptions = $expectedRequestOptions;

        return $self;
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

        foreach ($this->expectedRequestOptions[RequestOptions::HEADERS] ?? [] as $headerName => $headerValue) {
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
