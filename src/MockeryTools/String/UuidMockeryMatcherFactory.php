<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use Mockery;
use Mockery\Matcher\Closure;
use Ramsey\Uuid\UuidInterface;

final class UuidMockeryMatcherFactory
{
    public static function create(string $expected): Closure
    {
        return Mockery::on(
            static function (UuidInterface $uuid) use ($expected): bool {
                return $uuid->toString() === $expected;
            }
        );
    }
}
