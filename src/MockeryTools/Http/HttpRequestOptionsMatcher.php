<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use Mockery\Matcher\MatcherAbstract;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use function is_array;
use function ksort;

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
        if (!is_array($actual)) {
            return false;
        }

        if (!$this->containsAllExpectedOptions($actual, $this->expectedRequestOptions)) {
            return false;
        }

        if ($this->expectedRequestData === null) {
            return true;
        }

        $givenDataUsed = $actual[RequestOptions::JSON] ?? Json::decode($actual[RequestOptions::BODY] ?? '{}', Json::FORCE_ARRAY);
        try {
            $expectedRequestDataAsJson = Json::encode($this->recursiveKsort($this->expectedRequestData));
            $givenRequestDataAsJson = Json::encode($this->recursiveKsort($givenDataUsed));
        } catch (JsonException $exception) {
            return false;
        }

        return $expectedRequestDataAsJson === $givenRequestDataAsJson;
    }


    /**
     * @param mixed[] $actualOptions
     * @param mixed[] $expectedOptions
     */
    private function containsAllExpectedOptions(array $actualOptions, array $expectedOptions): bool
    {
        foreach ($expectedOptions as $expectedOptionName => $expectedOptionValue) {
            $actualOptionValue = $actualOptions[$expectedOptionName];

            if (!isset($actualOptionValue)) {
                return false;
            }

            if (!is_array($expectedOptionValue) && $actualOptionValue !== $expectedOptionValue) {
                return false;
            }

            if (is_array($expectedOptionValue) && !is_array($actualOptionValue)) {
                return false;
            }

            if (is_array($expectedOptionValue) && !$this->containsAllExpectedOptions($actualOptionValue, $expectedOptionValue)) {
                return false;
            }
        }

        return true;
    }


    public function __toString(): string
    {
        return '<HttpRequestOptions>';
    }


    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    private function recursiveKsort(array $array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveKsort($value);
            }
        }
        ksort($array);

        return $array;
    }
}
