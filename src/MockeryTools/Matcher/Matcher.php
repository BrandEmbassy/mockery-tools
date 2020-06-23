<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Matcher;

use BrandEmbassy\MockeryTools\DateTime\DateTimeAsAtomMatcher;
use BrandEmbassy\MockeryTools\Http\HttpRequestMatcher;
use BrandEmbassy\MockeryTools\String\StringStartsWithMatcher;
use BrandEmbassy\MockeryTools\String\UuidMatcher;
use BrandEmbassy\MockeryTools\Uri\UriMatcher;

final class Matcher
{
    public static function stringStartsWith(string $expectedStartsWith): StringStartsWithMatcher
    {
        return new StringStartsWithMatcher($expectedStartsWith);
    }


    public static function uuid(string $expectedUuid): UuidMatcher
    {
        return new UuidMatcher($expectedUuid);
    }


    public static function dateTimeAsAtom(string $expectedDateTimeInAtom): DateTimeAsAtomMatcher
    {
        return new DateTimeAsAtomMatcher($expectedDateTimeInAtom);
    }


    public static function uri(string $expectedUri): UriMatcher
    {
        return new UriMatcher($expectedUri);
    }


    /**
     * @param string[][] $expectedHeaders
     */
    public static function httpRequest(
        string $expectedMethod,
        string $expectedUri,
        array $expectedHeaders = [],
        string $expectedBody = ''
    ): HttpRequestMatcher {
        return new HttpRequestMatcher($expectedMethod, $expectedUri, $expectedHeaders, $expectedBody);
    }
}
