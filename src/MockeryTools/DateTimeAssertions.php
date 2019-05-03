<?php declare(strict_types = 1);

namespace BE\MockeryTools;

use DateTimeImmutable;

trait DateTimeAssertions
{
    public static function assertDateTimeTimestampsEquals(
        DateTimeImmutable $expectedDateTimeImmutable,
        DateTimeImmutable $dateTimeImmutable
    ): void {
        self::assertEquals($expectedDateTimeImmutable->getTimestamp(), $dateTimeImmutable->getTimestamp());
    }
}
