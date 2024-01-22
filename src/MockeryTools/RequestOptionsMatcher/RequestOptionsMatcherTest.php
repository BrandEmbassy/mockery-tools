<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\RequestOptionsMatcher;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RequestOptionsMatcherTest extends TestCase
{
    public function testCreateWithBody(): void
    {
        $matcher = RequestOptionsMatcher::createWithBody(
            [
                'dataToEncode' => 123,
                'moreData' => '456',
            ],
        );

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
            ],
        ];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createWithBodyAndHeaders(
            [
                'dataToEncode' => 123,
                'moreData' => '456',
            ],
            ['Accept' => 'application/json'],
        );

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
            RequestOptions::HEADERS => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        ];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithStringBody(): void
    {
        $matcher = RequestOptionsMatcher::createWithStringBody('{"dataToEncode":123,"moreData":"456"}');

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithStringBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createWithStringBodyAndHeaders(
            '{"dataToEncode":123,"moreData":"456"}',
            ['Accept' => 'application/json'],
        );

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithJsonBody(): void
    {
        $matcher = RequestOptionsMatcher::createWithJsonBody(
            [
                'dataToEncode' => 1.23,
                'moreData' => '456',
            ],
        );

        $matchingData = [
            RequestOptions::JSON => [
                'dataToEncode' => 1.23,
                'moreData' => '456',
            ],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithJsonBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createWithJsonBodyAndHeaders(
            [
                'dataToEncode' => 1.23,
                'moreData' => '456',
            ],
            ['Accept' => 'application/json'],
        );

        $matchingData = [
            RequestOptions::JSON => [
                'dataToEncode' => 1.23,
                'moreData' => '456',
            ],
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithEmptyBody(): void
    {
        $matcher = RequestOptionsMatcher::createWithEmptyBody();

        $matchingData = [];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithEmptyBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createWithEmptyBodyAndHeaders(['Accept' => 'application/json']);

        $matchingData = [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreate(): void
    {
        $matcher = RequestOptionsMatcher::create(
            [
                RequestOptions::CERT => 'cert',
                RequestOptions::BODY => '2',
            ],
        );

        $matchingData = [
            RequestOptions::CERT => 'cert',
            RequestOptions::BODY => '2',
        ];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateWithConversionFromJsonToBodyOption(): void
    {
        $matcher = RequestOptionsMatcher::createWithConversionFromJsonToBodyOption([
            RequestOptions::JSON => [
                'dataToEncode' => 1.23,
                'moreData' => '456',
            ],
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ]);

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":1.23,"moreData":"456"}',
            RequestOptions::HEADERS => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testExpectedDataIsReordered(): void
    {
        $matcher = RequestOptionsMatcher::create([
            RequestOptions::HEADERS => [],
            RequestOptions::JSON => [
                'z' => 'z',
                'a' => 'a',
            ],
            RequestOptions::BODY => '{"moreData":"456","dataToEncode":1.23}',
        ]);

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":1.23,"moreData":"456"}',
            RequestOptions::JSON => [
                'a' => 'a',
                'z' => 'z',
            ],
            RequestOptions::HEADERS => [],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testActualDataIsReordered(): void
    {
        $matcher = RequestOptionsMatcher::create([
            RequestOptions::BODY => '{"dataToEncode":1.23,"moreData":"456"}',
            RequestOptions::JSON => [
                'a' => 'a',
                'z' => 'z',
            ],
            RequestOptions::HEADERS => [],
        ]);

        $matchingData = [
            RequestOptions::HEADERS => [],
            RequestOptions::JSON => [
                'z' => 'z',
                'a' => 'a',
            ],
            RequestOptions::BODY => '{"moreData":"456","dataToEncode":1.23}',
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testWithHeader(): void
    {
        $matcher = RequestOptionsMatcher::createWithEmptyBody()->withHeader('Accept', 'application/json');

        $matchingData = [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testWithRequestOption(): void
    {
        $matcher = RequestOptionsMatcher::createWithEmptyBody()->withRequestOption(RequestOptions::CERT, 'cert');

        $matchingData = [
            RequestOptions::CERT => 'cert',
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }
}
