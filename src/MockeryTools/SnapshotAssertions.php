<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools;

use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use function str_replace;

final class SnapshotAssertions
{
    public static function assertSnapshot(string $snapshotFile, string $testedOutput): void
    {
        $snapshot = FileLoader::loadAsString($snapshotFile);

        $normalizedSnapshot = self::normalizeHtmlInput($snapshot);
        $testedOutput = self::normalizeHtmlInput($testedOutput);

        Assert::assertSame($normalizedSnapshot, $testedOutput);
    }


    public static function assertResponseSnapshot(string $snapshotFile, ResponseInterface $response): void
    {
        self::assertSnapshot($snapshotFile, (string)$response->getBody());
    }


    private static function normalizeHtmlInput(string $input): string
    {
        return str_replace(['  ', "\n", "\r", "\t"], '', $input);
    }
}
