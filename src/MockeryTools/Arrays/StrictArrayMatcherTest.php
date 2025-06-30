<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Arrays;

use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class StrictArrayMatcherTest extends TestCase
{
    /**
     * @dataProvider arrayMatchesProvider
     *
     * @param mixed[] $expected
     * @param mixed[] $actual
     */
    public function testMatches(array $expected, array $actual, bool $result): void
    {
        $matcher = new StrictArrayMatcher($expected);

        Assert::assertSame($result, $matcher->match($actual));
    }


    /**
     * @return mixed[][]
     */
    public static function arrayMatchesProvider(): array
    {
        return [
            'empty arrays match' => [
                'expected' => [],
                'actual' => [],
                'result' => true,
            ],
            'same arrays match (int)' => [
                'expected' => [1, 2, 3],
                'actual' => [1, 2, 3],
                'result' => true,
            ],
            'same arrays match (float)' => [
                'expected' => [1.0, 2.0, 3.0],
                'actual' => [1.0, 2.0, 3.0],
                'result' => true,
            ],
            'same arrays match (string)' => [
                'expected' => ['1', '2', '3'],
                'actual' => ['1', '2', '3'],
                'result' => true,
            ],
            'same arrays match (combined)' => [
                'expected' => [1, 2.0, '3', [null, []]],
                'actual' => [1, 2.0, '3', [null, []]],
                'result' => true,
            ],
            'different types do not match (int/float)' => [
                'expected' => [1, 2, 3],
                'actual' => [1.0, 2.0, 3.0],
                'result' => false,
            ],
            'different types do not match (int/string)' => [
                'expected' => [1, 2, 3],
                'actual' => ['1', '2', '3'],
                'result' => false,
            ],
            'arrays in different order do not match' => [
                'expected' => [1, 2, 3],
                'actual' => [3, 2, 1],
                'result' => false,
            ],
            'arrays in different order do not match even if multidimensional' => [
                'expected' => [[[1, 2, 3]]],
                'actual' => [[[3, 2, 1]]],
                'result' => false,
            ],
            'associative arrays in different order match' => [
                'expected' => ['first' => 1, 'second' => 2],
                'actual' => ['second' => 2, 'first' => 1],
                'result' => true,
            ],
            'associative arrays in different order match even if multidimensional' => [
                'expected' => [[['first' => 1, 'second' => 2]]],
                'actual' => [[['second' => 2, 'first' => 1]]],
                'result' => true,
            ],
            'objects do not match if they are different instances' => [
                'expected' => [new Exception()],
                'actual' => [new Exception()],
                'result' => false,
            ],
            'objects match if they are the same instance' => [
                'expected' => [$exception = new Exception()],
                'actual' => [$exception],
                'result' => true,
            ],
        ];
    }


    public function testNoMatchIfNotArray(): void
    {
        $matcher = new StrictArrayMatcher([]);

        $actual = null;

        Assert::assertFalse($matcher->match($actual));
    }
}
