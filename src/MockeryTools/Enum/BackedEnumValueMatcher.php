<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use Mockery\Matcher\MatcherInterface;
use ReflectionEnum;
use function assert;

/**
 * @final
 */
class BackedEnumValueMatcher implements MatcherInterface
{
    protected int|string $expected;


    public function __construct(string|int $expected)
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
        $rEnum = new ReflectionEnum($actual);
        assert($rEnum->isBacked());

        return $actual->value === $this->expected;
    }


    public function __toString(): string
    {
        return '<EnumValue:' . $this->expected . '>';
    }
}
