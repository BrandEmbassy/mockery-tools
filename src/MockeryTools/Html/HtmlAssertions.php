<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Html;

use PHPUnit\Framework\Assert;
use function str_replace;

final class HtmlAssertions
{
    public static function assertSameHtmlStrings(string $expectedHtml, string $actualHtml): void
    {
        $normalizedExpectedHtml = self::normalizeHtmlInput($expectedHtml);
        $normalizedActualHtml = self::normalizeHtmlInput($actualHtml);

        Assert::assertSame($normalizedExpectedHtml, $normalizedActualHtml);
    }


    private static function normalizeHtmlInput(string $input): string
    {
        return str_replace(['  ', "\n", "\r", "\t"], '', $input);
    }
}
