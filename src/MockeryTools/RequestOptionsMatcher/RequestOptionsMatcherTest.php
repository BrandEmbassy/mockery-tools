<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\RequestOptionsMatcher;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class RequestOptionsMatcherTest extends TestCase
{
    public function testCreateForRequestWithBodyToEncode(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithBodyToEncode(
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


    public function testCreateForRequestWithBodyToEncodeAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithBodyToEncodeAndHeaders(
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


    public function testCreateForRequestWithBody(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithBody('{"dataToEncode":123,"moreData":"456"}');

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateForRequestWithBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithBodyAndHeaders(
            '{"dataToEncode":123,"moreData":"456"}',
            ['Accept' => 'application/json'],
        );

        $matchingData = [
            RequestOptions::BODY => '{"dataToEncode":123,"moreData":"456"}',
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateForRequestWithJsonBody(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithJsonBody(
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


    public function testCreateForRequestWithJsonBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithJsonBodyAndHeaders(
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


    public function testCreateForRequestWithEmptyBody(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithEmptyBody();

        $matchingData = [];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateForRequestWithEmptyBodyAndHeaders(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithEmptyBodyAndHeaders(['Accept' => 'application/json']);

        $matchingData = [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];

        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testCreateForRequestOptions(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestOptions(
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


    public function testCreateForRequestOptionsWithConversionFromJsonToBodyOption(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestOptionsWithConversionFromJsonToBodyOption([
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
        $matcher = RequestOptionsMatcher::createForRequestOptions([
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
        $matcher = RequestOptionsMatcher::createForRequestOptions([
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
        $matcher = RequestOptionsMatcher::createForRequestWithEmptyBody()->withHeader('Accept', 'application/json');

        $matchingData = [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }


    public function testWithRequestOption(): void
    {
        $matcher = RequestOptionsMatcher::createForRequestWithEmptyBody()->withRequestOption(RequestOptions::CERT, 'cert');

        $matchingData = [
            RequestOptions::CERT => 'cert',
        ];
        Assert::assertTrue($matcher->match($matchingData));
    }
}
