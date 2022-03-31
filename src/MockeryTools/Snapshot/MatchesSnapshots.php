<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Snapshot;

use BrandEmbassy\MockeryTools\Http\ResponseAssertions;
use Psr\Http\Message\ResponseInterface;
use Spatie\Snapshots\MatchesSnapshots as BaseMatchesSnapshots;

trait MatchesSnapshots
{
    use BaseMatchesSnapshots;


    public function assertResponseMatchesHtmlSnapshot(ResponseInterface $response, int $expectedStatusCode = 200): void
    {
        $this->assertMatchesHtmlSnapshot((string)$response->getBody());
        ResponseAssertions::assertResponseStatusCode($expectedStatusCode, $response);
    }
}
