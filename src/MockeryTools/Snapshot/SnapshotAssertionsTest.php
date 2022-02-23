<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Snapshot;

use Nette\Utils\FileSystem;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class SnapshotAssertionsTest extends TestCase
{
    public function testSnapshotIsAsserted(): void
    {
        $testedOutput = FileSystem::read(__DIR__ . '/__fixtures__/testedOutput.html');

        SnapshotAssertions::assertSnapshot(
            __DIR__ . '/__fixtures__/snapshot.html',
            $testedOutput
        );
    }


    public function testSnapshotWithReplacedValuesIsAsserted(): void
    {
        $testedOutput = FileSystem::read(__DIR__ . '/__fixtures__/testedOutput.html');

        SnapshotAssertions::assertSnapshotAndReplace(
            __DIR__ . '/__fixtures__/snapshotWithReplacedValues.html',
            $testedOutput,
            [
                'value1' => 'nonumes',
                'value2' => 'ceteros',
                'value3' => '1234 ',
            ]
        );
    }
}
