<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use Mockery\Matcher\MatcherInterface;
use Ramsey\Uuid\UuidInterface;
use function assert;

/**
 * @final
 */
class UuidMatcher implements MatcherInterface
{
    private string $expected;


    public function __construct(string $expected)
    {
        $this->expected = $expected;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof UuidInterface);

        return $actual->toString() === $this->expected;
    }


    public function __toString(): string
    {
        return '<Uuid:' . $this->expected . '>';
    }
}
