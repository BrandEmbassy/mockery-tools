<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Snapshot;

use BrandEmbassy\MockeryTools\FileLoader;
use BrandEmbassy\MockeryTools\Html\HtmlAssertions;
use Psr\Http\Message\ResponseInterface;
use function array_keys;
use function array_map;
use function array_values;
use function sprintf;
use function str_replace;

/**
 * @final
 */
class SnapshotAssertions
{
    public static function assertSnapshot(string $snapshotFile, string $testedOutput): void
    {
        $snapshot = FileLoader::loadAsString($snapshotFile);

        HtmlAssertions::assertSameHtmlStrings($snapshot, $testedOutput);
    }


    /**
     * @param array<string, string> $valuesToReplace
     */
    public static function assertSnapshotAndReplace(
        string $snapshotFile,
        string $testedOutput,
        array $valuesToReplace
    ): void {
        $snapshot = FileLoader::loadAsString($snapshotFile);
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

        HtmlAssertions::assertSameHtmlStrings($snapshotWithReplacedValues, $testedOutput);
    }


    /**
     * @param array<string, string> $valuesToReplace
     */
    public static function assertResponseSnapshot(
        string $snapshotFile,
        ResponseInterface $response,
        array $valuesToReplace = []
    ): void {
        if ($valuesToReplace !== []) {
            self::assertSnapshotAndReplace($snapshotFile, (string)$response->getBody(), $valuesToReplace);

            return;
        }

        self::assertSnapshot($snapshotFile, (string)$response->getBody());
    }
}
