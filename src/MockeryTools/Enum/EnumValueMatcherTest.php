<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use MabeEnum\Enum;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class EnumValueMatcherTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    /**
     * @dataProvider uuidDataProvider
     *
     * @param mixed $expectedEnumValue
     */
    public function testMatching(bool $expectedResult, $expectedEnumValue, Enum $enumToMatch): void
    {
        $matcher = new EnumValueMatcher($expectedEnumValue);

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
                'expectedEnumValue' => TestOnlyEnum::STRING_VALUE,
                'enumToMatch' => TestOnlyEnum::get(TestOnlyEnum::STRING_VALUE),
            ],
            'Matching integer value' => [
                'expectedResult' => true,
                'expectedEnumValue' => TestOnlyEnum::INTEGER_VALUE,
                'enumToMatch' => TestOnlyEnum::get(TestOnlyEnum::INTEGER_VALUE),
            ],
            'Not matching string values' => [
                'expectedResult' => false,
                'expectedEnumValue' => 'another-string-value-1',
                'enumToMatch' => TestOnlyEnum::get(TestOnlyEnum::STRING_VALUE),
            ],
            'Not matching data types' => [
                'expectedResult' => false,
                'expectedEnumValue' => TestOnlyEnum::INTEGER_VALUE,
                'enumToMatch' => TestOnlyEnum::get(TestOnlyEnum::INTEGER_AS_STRING_VALUE),
            ],
        ];
    }
}
