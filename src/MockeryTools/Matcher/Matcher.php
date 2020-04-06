<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Matcher;

use BrandEmbassy\MockeryTools\DateTime\DateTimeAsAtomMatcher;
use BrandEmbassy\MockeryTools\String\StringStartsWithMatcher;
use BrandEmbassy\MockeryTools\Uri\UriMatcher;
use Psr\Http\Message\UriInterface;

final class Matcher
{
    public static function stringStartsWith(string $expectedStartsWith): StringStartsWithMatcher
    {
        return new StringStartsWithMatcher($expectedStartsWith);
    }


    public static function dateTimeAsAtom(string $expectedDateTimeInAtom): DateTimeAsAtomMatcher
    {
        return new DateTimeAsAtomMatcher($expectedDateTimeInAtom);
    }


    public static function uri(string $expectedUri): UriMatcher
    {
        return new UriMatcher($expectedUri);
    }
}
