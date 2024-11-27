<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use BackedEnum;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class BackedEnumValueMatcherTest extends TestCase
{
    /**
     * @dataProvider uuidDataProvider
     */
    public function testMatching(bool $expectedResult, string|int $expectedEnumValue, BackedEnum $enumToMatch): void
    {
        $matcher = new BackedEnumValueMatcher($expectedEnumValue);

        $result = $matcher->match($enumToMatch);

        Assert::assertSame($expectedResult, $result);
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public static function uuidDataProvider(): array
    {
        return [
            'Matching string value' => [
                'expectedResult' => true,
                'expectedEnumValue' => TestOnlyBackedStringEnum::STRING_VALUE->value,
                'enumToMatch' => TestOnlyBackedStringEnum::STRING_VALUE,
            ],
            'Matching integer value' => [
                'expectedResult' => true,
                'expectedEnumValue' => TestOnlyBackedIntEnum::INTEGER_VALUE->value,
                'enumToMatch' => TestOnlyBackedIntEnum::INTEGER_VALUE,
            ],
            'Not matching string values' => [
                'expectedResult' => false,
                'expectedEnumValue' => 'another-string-value-1',
                'enumToMatch' => TestOnlyBackedStringEnum::STRING_VALUE,
            ],
            'Not matching data types' => [
                'expectedResult' => false,
                'expectedEnumValue' => TestOnlyBackedIntEnum::INTEGER_VALUE->value,
                'enumToMatch' => TestOnlyBackedStringEnum::INTEGER_AS_STRING_VALUE,
            ],
        ];
    }
}
