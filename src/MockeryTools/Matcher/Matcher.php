<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Matcher;

final class Matcher
{
    public static function stringStartsWith(string $expected): StringStartsWith
    {
        return new StringStartsWith($expected);
    }
}
