<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Html;

use PHPUnit\Framework\TestCase;

final class HtmlAssertionsTest extends TestCase
{
    private const EXPECTED_HTML_STRING = '<p>Lorem ipsum dolor sit amet.</p>';
    private const ACTUAL_HTML_STRING = "<p>\rLorem\t ipsum\n dolor sit amet  .</p>";


    public function testHtmlStringsAreSame(): void
    {
        HtmlAssertions::assertSameHtmlStrings(self::EXPECTED_HTML_STRING, self::ACTUAL_HTML_STRING);
    }
}
