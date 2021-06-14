<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Snapshot;

use BrandEmbassy\MockeryTools\FileLoader;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use function array_keys;
use function array_map;
use function array_values;
use function sprintf;
use function str_replace;

final class SnapshotAssertions
{
    public static function assertSnapshot(string $snapshotFile, string $testedOutput): void
    {
        $normalizedSnapshot = self::loadNormalizedSnapshot($snapshotFile);
        $testedOutput = self::normalizeHtmlInput($testedOutput);

        Assert::assertSame($normalizedSnapshot, $testedOutput);
    }


    /**
     * @param array<string, string> $valuesToReplace
     */
    public static function assertSnapshotAndReplace(
        string $snapshotFile,
        string $testedOutput,
        array $valuesToReplace
    ): void {
        $snapshot = self::loadNormalizedSnapshot($snapshotFile);
        $keys = array_map(
            static function (string $key): string {
                return sprintf('{{%s}}', $key);
            },
            array_keys($valuesToReplace)
        );
        $snapshotWithReplacedValues = str_replace(
            $keys,
            array_values($valuesToReplace),
            $snapshot
        );

        $testedOutput = self::normalizeHtmlInput($testedOutput);

        Assert::assertSame($snapshotWithReplacedValues, $testedOutput);
    }


    public static function assertResponseSnapshot(string $snapshotFile, ResponseInterface $response): void
    {
        self::assertSnapshot($snapshotFile, (string)$response->getBody());
    }


    private static function normalizeHtmlInput(string $input): string
    {
        return str_replace(['  ', "\n", "\r", "\t"], '', $input);
    }


    private static function loadNormalizedSnapshot(string $snapshotFile): string
    {
        $snapshot = FileLoader::loadAsString($snapshotFile);

        return self::normalizeHtmlInput($snapshot);
    }
}
