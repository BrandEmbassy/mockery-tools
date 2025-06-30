<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @final
 */
class HttpRequestOptionsMatcherTest extends TestCase
{
    /**
     * @dataProvider matchRequestOptionsDataProvider
     *
     * @param mixed[] $expectedRequestOptions
     * @param mixed[] $actual
     */
    public function testMatchRequestOptions(array $actual, array $expectedRequestOptions, bool $expectedResult): void
    {
        $httpRequestOptionMatcher = HttpRequestOptionsMatcher::create($expectedRequestOptions);

        Assert::assertSame($expectedResult, $httpRequestOptionMatcher->match($actual));
    }


    /**
     * @return mixed[]
     */
    public static function matchRequestOptionsDataProvider(): array
    {
        return [
            [
                'actual' => [],
                'expectedRequestOptions' => [],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [],
                ],
                'expectedRequestOptions' => [],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                ],
                'expectedRequestOptions' => [],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                ],
                'expectedRequestOptions' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                ],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                        'Authorization' => 'Bearer abcd1234',
                    ],
                ],
                'expectedRequestOptions' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                ],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                ],
                'expectedRequestOptions' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/plain',
                    ],
                ],
                'expectedResult' => false,
            ],
            [
                'actual' => [
                    RequestOptions::TIMEOUT => 5,
                ],
                'expectedRequestOptions' => [],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                    RequestOptions::TIMEOUT => 5,
                ],
                'expectedRequestOptions' => [],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                    RequestOptions::TIMEOUT => 5,
                ],
                'expectedRequestOptions' => [
                    RequestOptions::TIMEOUT => 5,
                ],
                'expectedResult' => true,
            ],
            [
                'actual' => [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'text/html',
                    ],
                    RequestOptions::TIMEOUT => 5,
                ],
                'expectedRequestOptions' => [
                    RequestOptions::TIMEOUT => 10,
                ],
                'expectedResult' => false,
            ],
        ];
    }


    /**
     * @dataProvider jsonRequestDataProvider
     *
     * @param array<string|int, mixed>|stdClass|null $expectedRequestData
     * @param array<string, mixed> $actual
     */
    public function testMatchesRequestData(
        bool $expectedResult,
        array|stdClass|null $expectedRequestData,
        array $actual,
    ): void {
        $httpRequestOptionMatcher = HttpRequestOptionsMatcher::create(
            [],
            $expectedRequestData,
        );

        Assert::assertSame($expectedResult, $httpRequestOptionMatcher->match($actual));
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public static function jsonRequestDataProvider(): array
    {
        return [
            'Data matched despite of order' => [
                'expectedResult' => true,
                'expectedRequestData' => [
                    'b' => 2,
                    'c' => 3,
                    'a' => 1,
                ],
                'actual' => [
                    RequestOptions::JSON => [
                        'a' => 1,
                        'b' => 2,
                        'c' => 3,
                    ],
                ],
            ],
            'JSON matched despite of order' => [
                'expectedResult' => true,
                'expectedRequestData' => [
                    'b' => 2,
                    'c' => 3,
                    'a' => 1,
                ],
                'actual' => [
                    RequestOptions::BODY => '{"a": 1, "b": 2, "c": 3}',
                ],
            ],
            'Data matched despite of order - tree' => [
                'expectedResult' => true,
                'expectedRequestData' => [
                    'b' => ['beta' => 'qwerty', 'alpha' => 'loremipsum'],
                    'c' => ['alpha' => 'loremipsum', 'beta' => 'qwerty'],
                    'a' => [100, 200, 300],
                ],
                'actual' => [
                    RequestOptions::JSON => [
                        'a' => [100, 200, 300],
                        'b' => ['alpha' => 'loremipsum', 'beta' => 'qwerty'],
                        'c' => ['alpha' => 'loremipsum', 'beta' => 'qwerty'],
                    ],
                ],
            ],
            'JSON matched despite of order - tree' => [
                'expectedResult' => true,
                'expectedRequestData' => [
                    'b' => ['beta' => 'qwerty', 'alpha' => 'loremipsum'],
                    'c' => ['alpha' => 'loremipsum', 'beta' => 'qwerty'],
                    'a' => [100, 200, 300],
                ],
                'actual' => [
                    RequestOptions::BODY => '{"a":[100,200,300],"b":{"alpha":"loremipsum","beta":"qwerty"},"c":{"alpha":"loremipsum","beta":"qwerty"}}',
                ],
            ],
            'Data unmatched' => [
                'expectedResult' => false,
                'expectedRequestData' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                'actual' => [
                    RequestOptions::JSON => [
                        'a' => 1,
                        'b' => 5,
                        'c' => 3,
                    ],
                ],
            ],
            'JSON unmatched' => [
                'expectedResult' => false,
                'expectedRequestData' => [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ],
                'actual' => [
                    RequestOptions::BODY => '{"a": 1, "b": 5, "c": 3}',
                ],
            ],
            'Empty data matched type' => [
                'expectedResult' => true,
                'expectedRequestData' => [],
                'actual' => [
                    RequestOptions::JSON => [],
                ],
            ],
            'Empty data unmatched type' => [
                'expectedResult' => false,
                'expectedRequestData' => new stdClass(),
                'actual' => [
                    RequestOptions::JSON => [],
                ],
            ],
            'Empty JSON matched type' => [
                'expectedResult' => true,
                'expectedRequestData' => new stdClass(),
                'actual' => [
                    RequestOptions::BODY => '{}',
                ],
            ],
            'Empty JSON unmatched type' => [
                'expectedResult' => false,
                'expectedRequestData' => [],
                'actual' => [
                    RequestOptions::BODY => '{}',
                ],
            ],
            'Matched empty data field' => [
                'expectedResult' => false,
                'expectedRequestData' => [
                    'emptyField' => [],
                ],
                'actual' => [
                    RequestOptions::JSON => [],
                ],
            ],
            'Matched empty JSON field' => [
                'expectedResult' => false,
                'expectedRequestData' => [
                    'emptyField' => new stdClass(),
                ],
                'actual' => [
                    RequestOptions::BODY => '{}',
                ],
            ],
        ];
    }
}
