<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use BrandEmbassy\MockeryTools\Matcher\Matcher;
use Mockery;
use Mockery\Matcher\Closure;
use Ramsey\Uuid\UuidInterface;

final class UuidMockeryMatcherFactory
{
    /**
     * @deprecated use Matcher::uuid($expected) directly
     */
    public static function create(string $expected): Closure
    {
        return Mockery::on(
            static function (UuidInterface $uuid) use ($expected): bool {
                $uuidMatcher = Matcher::uuid($expected);

                return $uuidMatcher->match($expected);
            }
        );
    }
}
