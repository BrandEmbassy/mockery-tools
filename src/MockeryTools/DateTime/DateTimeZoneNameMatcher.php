<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\DateTime;

use DateTimeZone;
use Mockery\Matcher\MatcherAbstract;
use function assert;

/**
 * @final
 */
class DateTimeZoneNameMatcher extends MatcherAbstract
{
    private string $expectedDateTimeZoneName;


    public function __construct(string $expectedDateTimeZoneName)
    {
        parent::__construct();
        $this->expectedDateTimeZoneName = $expectedDateTimeZoneName;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof DateTimeZone);

        return $actual->getName() === $this->expectedDateTimeZoneName;
    }


    public function __toString(): string
    {
        return '<DateTimeZoneNameMatch[' . $this->expectedDateTimeZoneName . ']>';
    }
}
