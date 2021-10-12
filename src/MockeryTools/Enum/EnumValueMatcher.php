<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use MabeEnum\Enum;
use Mockery\Matcher\MatcherAbstract;
use function assert;

final class EnumValueMatcher extends MatcherAbstract
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof Enum);

        return $actual->getValue() === $this->_expected;
    }


    public function __toString(): string
    {
        return '<EnumValue:' . $this->_expected . '>';
    }
}
