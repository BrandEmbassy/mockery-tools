<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use MabeEnum\Enum;
use Mockery\Matcher\MatcherInterface;
use function assert;

/**
 * @final
 */
class EnumValueMatcher implements MatcherInterface
{
    protected mixed $expected;


    public function __construct(mixed $expected = null)
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
        assert($actual instanceof Enum);

        return $actual->getValue() === $this->expected;
    }


    public function __toString(): string
    {
        return '<EnumValue:' . $this->expected . '>';
    }
}
