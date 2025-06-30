<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use BackedEnum;
use Mockery\Matcher\MatcherInterface;

/**
 * @final
 */
class BackedEnumCaseMatcher implements MatcherInterface
{
    protected BackedEnum $expected;


    public function __construct(BackedEnum $expected)
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
        return $actual === $this->expected;
    }


    public function __toString(): string
    {
        return '<EnumValue:' . $this->expected->value . '>';
    }
}
