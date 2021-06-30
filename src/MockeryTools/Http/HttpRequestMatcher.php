<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use Mockery\Matcher\MatcherAbstract;
use Psr\Http\Message\RequestInterface;
use function assert;
use function is_array;

final class HttpRequestMatcher extends MatcherAbstract
{
    /**
     * @var string
     */
    private $expectedMethod;

    /**
     * @var string
     */
    private $expectedUri;

    /**
     * @var array<string, string|array<int, string>>
     */
    private $expectedHeaders;

    /**
     * @var string
     */
    private $expectedBody;


    /**
     * @param array<string, string|array<int, string>> $expectedHeaders
     */
    public function __construct(
        string $expectedMethod,
        string $expectedUri,
        array $expectedHeaders = [],
        string $expectedBody = ''
    ) {
        parent::__construct();

        $this->expectedMethod = $expectedMethod;
        $this->expectedUri = $expectedUri;
        $this->expectedBody = $expectedBody;

        $this->expectedHeaders = [];
        foreach ($expectedHeaders as $headerName => $headerValue) {
            $this->expectedHeaders[$headerName] = is_array($headerValue) ? $headerValue : [$headerValue];
        }
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof RequestInterface);

        $isMatching = $actual->getMethod() === $this->expectedMethod;
        $isMatching = $isMatching && (string)$actual->getUri() === $this->expectedUri;
        $isMatching = $isMatching && $this->containsExpectedHeaders($actual);
        $isMatching = $isMatching && (string)$actual->getBody() === $this->expectedBody;

        return $isMatching;
    }


    public function __toString(): string
    {
        return '<HttpRequest:' . $this->expectedUri . '>';
    }


    private function containsExpectedHeaders(RequestInterface $request): bool
    {
        $requestHeaders = $request->getHeaders();
        foreach ($this->expectedHeaders as $headerName => $headerValues) {
            if (!isset($requestHeaders[$headerName])) {
                return false;
            }
            if ($requestHeaders[$headerName][0] !== $headerValues[0]) {
                return false;
            }
        }

        return true;
    }
}
