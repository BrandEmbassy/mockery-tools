<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class HttpRequestOptionsMatcherTest extends TestCase
{
    /**
     * @dataProvider matchRequestOptionsDataProvider
     *
     * @param mixed[] $actual
     * @param mixed[] $expectedRequestOptions
     */
    public function testMatchRequestOptions(array $actual, array $expectedRequestOptions, bool $expectedResult): void
    {
        $httpRequestOptionMatcher = HttpRequestOptionsMatcher::create($expectedRequestOptions);

        Assert::assertSame($expectedResult, $httpRequestOptionMatcher->match($actual));
    }


    /**
     * @return mixed[]
     */
    public function matchRequestOptionsDataProvider(): array
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
}
