<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

/**
 * @final
 */
class UuidAssertions
{
    public static function assertStringUuidEqualsUuid(string $expectedUuidString, UuidInterface $uuid): void
    {
        Assert::assertSame($expectedUuidString, $uuid->toString());
    }


    public static function assertUuidEqualsUuid(UuidInterface $expectedUuid, UuidInterface $uuid): void
    {
        Assert::assertSame($expectedUuid->toString(), $uuid->toString());
    }
}
