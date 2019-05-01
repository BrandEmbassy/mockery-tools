<?php declare(strict_types = 1);

namespace BE\MockeryTools;

use Nette\Utils\FileSystem;
use Psr\Http\Message\ResponseInterface;

trait SnapshotAssertions
{
    public static function assertSnapshot(string $snapshotFile, string $testedOutput): void
    {
        $snapshot = FileSystem::read($snapshotFile);

        $snapshot = str_replace(['  ', "\n", "\r", "\t"], '', $snapshot);
        $testedOutput = str_replace(['  ', "\n", "\r", "\t"], '', $testedOutput);

        self::assertEquals($snapshot, $testedOutput);
    }


    public static function assertResponseSnapshot(string $snapshotFile, ResponseInterface $response): void
    {
        self::assertSnapshot($snapshotFile, (string)$response->getBody());
    }
}
