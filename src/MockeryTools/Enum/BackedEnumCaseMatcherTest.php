<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use BackedEnum;
use BrandEmbassy\MockeryTools\Enum\__fixtures__\TestOnlyBackedIntEnum;
use BrandEmbassy\MockeryTools\Enum\__fixtures__\TestOnlyBackedStringAnotherEnum;
use BrandEmbassy\MockeryTools\Enum\__fixtures__\TestOnlyBackedStringEnum;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class BackedEnumCaseMatcherTest extends TestCase
{
    /**
     * @dataProvider matchingDataProvider
     */
    public function testMatching(bool $expectedResult, BackedEnum $expectedEnumCase, BackedEnum $enumToMatch): void
    {
        $matcher = new BackedEnumCaseMatcher($expectedEnumCase);

        $result = $matcher->match($enumToMatch);

        Assert::assertSame($expectedResult, $result);
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public static function matchingDataProvider(): array
    {
        return [
            'Matching case (value string)' => [
                'expectedResult' => true,
                'expectedEnumValue' => TestOnlyBackedStringEnum::STRING_VALUE,
                'enumToMatch' => TestOnlyBackedStringEnum::STRING_VALUE,
            ],
            'Matching case (integer case)' => [
                'expectedResult' => true,
                'expectedEnumValue' => TestOnlyBackedIntEnum::INTEGER_VALUE,
                'enumToMatch' => TestOnlyBackedIntEnum::INTEGER_VALUE,
            ],
            'Not matching - same string, different enum' => [
                'expectedResult' => false,
                'expectedEnumValue' => TestOnlyBackedStringAnotherEnum::STRING_VALUE,
                'enumToMatch' => TestOnlyBackedStringEnum::STRING_VALUE,
            ],
            'Not matching - string vs int, different enums' => [
                'expectedResult' => false,
                'expectedEnumValue' => TestOnlyBackedIntEnum::INTEGER_VALUE,
                'enumToMatch' => TestOnlyBackedStringEnum::INTEGER_AS_STRING_VALUE,
            ],
        ];
    }
}
