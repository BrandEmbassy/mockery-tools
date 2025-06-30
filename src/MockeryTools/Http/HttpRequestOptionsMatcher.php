<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use Mockery\Matcher\MatcherInterface;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use stdClass;
use function is_array;

/**
 * @final
 */
class HttpRequestOptionsMatcher implements MatcherInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $expectedRequestOptions;

    /**
     * @var mixed[]|stdClass|null
     */
    private array|stdClass|null $expectedRequestData;


    /**
     * @param array<string, string> $expectedHeaders
     * @param mixed[]|stdClass|null $expectedRequestData
     */
    public function __construct(
        array $expectedHeaders,
        array|stdClass|null $expectedRequestData = null,
    ) {
        $this->expectedRequestData = $expectedRequestData;
        $this->expectedRequestOptions = [RequestOptions::HEADERS => $expectedHeaders];
    }


    /**
     * @param mixed[]|stdClass|null $expectedRequestData
     * @param array<string, string> $expectedRequestOptions
     */
    public static function create(
        array $expectedRequestOptions,
        array|stdClass|null $expectedRequestData = null,
    ): self {
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

        // Beware !! (JSON == array) here
        $givenData = $actual[RequestOptions::JSON] ?? Json::decode($actual[RequestOptions::BODY] ?? '{}');
        try {
            $expectedRequestDataAsJson = \PHPUnit\Util\Json::canonicalize(
                Json::encode($this->expectedRequestData),
            )[1];
            $givenRequestDataAsJson = \PHPUnit\Util\Json::canonicalize(
                Json::encode($givenData),
            )[1];
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
}
