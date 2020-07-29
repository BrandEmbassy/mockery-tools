<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\DateTime;

use DateTimeImmutable;
use Mockery\Matcher\MatcherAbstract;
use function assert;

final class DateTimeAsTimestampMatcher extends MatcherAbstract
{
    /**
     * @var int
     */
    private $dateTimeTimestamp;


    public function __construct(int $dateTimeTimestamp)
    {
        parent::__construct();
        $this->dateTimeTimestamp = $dateTimeTimestamp;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        assert($actual instanceof DateTimeImmutable);

        return $actual->getTimestamp() === $this->dateTimeTimestamp;
    }


    public function __toString(): string
    {
        return '<DateTimeMatch[' . $this->dateTimeTimestamp . ']>';
    }
}
