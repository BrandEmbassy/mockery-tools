<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Matcher;

use BrandEmbassy\MockeryTools\String\StringStartsWithMatcher;

final class Matcher
{
    public static function stringStartsWith(string $expected): StringStartsWithMatcher
    {
        return new StringStartsWithMatcher($expected);
    }
}
