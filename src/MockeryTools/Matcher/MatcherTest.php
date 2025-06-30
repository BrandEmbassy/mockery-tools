<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Matcher;

use DateTimeZone;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class MatcherTest extends TestCase
{
    /**
     * @dataProvider dateTimeZoneNameMatcherProvider
     */
    public function testDateTimeZoneNameMatches(
        bool $expectedValue,
        string $dateTimeZoneNameToMatch,
        DateTimeZone $dateTimeZone
    ): void {
        $matcher = Matcher::dateTimeZoneName($dateTimeZoneNameToMatch);

        $isMatch = $matcher->match($dateTimeZone);

        Assert::assertSame($expectedValue, $isMatch);
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public static function dateTimeZoneNameMatcherProvider(): array
    {
        return [
            'Valid TZ does match' => [
                'expectedValue' => true,
                'dateTimeZoneNameToMatch' => 'Europe/Rome',
                'dateTimeZone' => new DateTimeZone('Europe/Rome'),
            ],
            'Valid TZ does not match' => [
                'expectedValue' => false,
                'dateTimeZoneNameToMatch' => 'Europe/Rome',
                'dateTimeZone' => new DateTimeZone('Europe/Prague'),
            ],
            'UTC does match' => [
                'expectedValue' => true,
                'dateTimeZoneNameToMatch' => 'UTC',
                'dateTimeZone' => new DateTimeZone('UTC'),
            ],
            'Invalid TZ does not match' => [
                'expectedValue' => false,
                'dateTimeZoneNameToMatch' => 'Tatoo/Tatooine',
                'dateTimeZone' => new DateTimeZone('Europe/Prague'),
            ],
        ];
    }
}
