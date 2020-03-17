<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools;

use DateTimeImmutable;
use PHPUnit\Framework\Assert;

final class DateTimeAssertions
{
    public static function assertDateTimeTimestampsEquals(
        DateTimeImmutable $expectedDateTimeImmutable,
        DateTimeImmutable $dateTimeImmutable
    ): void {
        Assert::assertSame($expectedDateTimeImmutable->getTimestamp(), $dateTimeImmutable->getTimestamp());
    }
}
