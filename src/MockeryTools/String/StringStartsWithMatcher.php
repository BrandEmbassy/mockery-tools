<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use Mockery\Matcher\MatcherAbstract;
use Nette\Utils\Strings;

/**
 * @property string $_expected
 *
 * @final
 */
class StringStartsWithMatcher extends MatcherAbstract
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
        return Strings::startsWith($actual, $this->_expected);
    }


    public function __toString(): string
    {
        return '<StringStartsWith:' . $this->_expected . '>';
    }
}
