<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use Mockery\Matcher\MatcherAbstract;
use Ramsey\Uuid\UuidInterface;
use function assert;

/**
 * @final
 */
class UuidMatcher extends MatcherAbstract
{
    public function __construct(string $expected)
    {
        parent::__construct($expected);
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

        return $actual->toString() === $this->_expected;
    }


    public function __toString(): string
    {
        return '<Uuid:' . $this->_expected . '>';
    }
}
