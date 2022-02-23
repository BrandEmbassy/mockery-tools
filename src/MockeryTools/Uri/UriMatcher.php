<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Uri;

use Mockery\Matcher\MatcherAbstract;

/**
 * @property string $_expected
 *
 * @final
 */
class UriMatcher extends MatcherAbstract
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
        return (string)$actual === $this->_expected;
    }


    public function __toString(): string
    {
        return '<Uri:' . $this->_expected . '>';
    }
}
