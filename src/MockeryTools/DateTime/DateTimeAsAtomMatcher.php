<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\DateTime;

use DateTime;
use DateTimeImmutable;
use Mockery\Matcher\MatcherAbstract;
use function assert;

/**
 * @final
 */
class DateTimeAsAtomMatcher extends MatcherAbstract
{
    private string $expectedDateTimeAsAtom;


    public function __construct(string $expectedDateTimeAsAtom)
    {
        parent::__construct();
        $this->expectedDateTimeAsAtom = $expectedDateTimeAsAtom;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof DateTimeImmutable);

        return $actual->format(DateTime::ATOM) === $this->expectedDateTimeAsAtom;
    }


    public function __toString(): string
    {
        return '<DateTimeMatch[' . $this->expectedDateTimeAsAtom . ']>';
    }
}
