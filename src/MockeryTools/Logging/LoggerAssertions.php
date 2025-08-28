<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Logging;

use Nette\StaticClass;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use Psr\Log\Test\TestLogger;

/**
 * @final
 */
class LoggerAssertions
{
    use StaticClass;

    final public const LOG_LEVEL_INFO = 'info';

    final public const LOG_LEVEL_DEBUG = 'debug';

    final public const LOG_LEVEL_WARNING = 'warning';

    private const MESSAGE_FIELD = 'message';

    private const CONTEXT_FIELD = 'context';


    /**
     * @param array<string, mixed> $contextToExpect
     */
    public static function assertHasLogRecord(
        TestLogger $logger,
        string $logLevel,
        int $logRecordIndex,
        string $messageToExpect,
        array $contextToExpect,
    ): void {
        $logRecord = self::getLogRecord($logger, $logLevel, $logRecordIndex);

        Assert::assertSame($messageToExpect, self::getLogRecordMessage($logRecord));
        Assert::assertSame($contextToExpect, self::getLogRecordContext($logRecord));
    }


    public static function assertHasLogRecordIgnoringContext(
        TestLogger $logger,
        string $logLevel,
        int $logRecordIndex,
        string $messageToExpect,
    ): void {
        $logRecord = self::getLogRecord($logger, $logLevel, $logRecordIndex);

        Assert::assertSame($messageToExpect, self::getLogRecordMessage($logRecord));
    }


    public static function assertHasLogRecordComparingContextToJsonFile(
        TestLogger $logger,
        string $logLevel,
        int $logRecordIndex,
        string $messageToExpect,
        string $contextSnapshotToExpect,
    ): void {
        $logRecord = self::getLogRecord($logger, $logLevel, $logRecordIndex);

        Assert::assertSame($messageToExpect, self::getLogRecordMessage($logRecord));
        Assert::assertJsonStringEqualsJsonFile($contextSnapshotToExpect, Json::encode(self::getLogRecordContext($logRecord)));
    }


    /**
     * @return array<string, mixed>
     */
    private static function getLogRecord(TestLogger $logger, string $logLevel, int $logRecordIndex): array
    {
        return $logger->recordsByLevel[$logLevel][$logRecordIndex];
    }


    /**
     * @param array<string, mixed> $logRecord
     */
    private static function getLogRecordMessage(array $logRecord): string
    {
        return $logRecord[self::MESSAGE_FIELD];
    }


    /**
     * @param array<string, mixed> $logRecord
     *
     * @return array<string, mixed>
     */
    private static function getLogRecordContext(array $logRecord): array
    {
        return $logRecord[self::CONTEXT_FIELD];
    }
}
