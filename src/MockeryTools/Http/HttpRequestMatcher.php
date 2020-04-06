<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use Mockery\Matcher\MatcherAbstract;
use Nette\Utils\Json;
use Psr\Http\Message\RequestInterface;
use function assert;

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
     * @var string[][]
     */
    private $expectedHeaders;

    /**
     * @var string
     */
    private $expectedBody;


    /**
     * @param string[][] $expectedHeaders
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
        $this->expectedHeaders = $expectedHeaders;
        $this->expectedBody = $expectedBody;
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

        $actualHeaders = Json::encode($actual->getHeaders());
        $expectedHeaders = Json::encode($this->expectedHeaders);

        $isMatching = $actual->getMethod() === $this->expectedMethod;
        $isMatching = $isMatching && (string)$actual->getUri() === $this->expectedUri;
        $isMatching = $isMatching && $actualHeaders === $expectedHeaders;
        $isMatching = $isMatching && (string)$actual->getBody() === $this->expectedBody;

        return $isMatching;
    }


    public function __toString(): string
    {
        return '<HttpRequest:' . $this->expectedUri . '>';
    }
}
