<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\MockeryVerbose;

use Nette\Utils\Strings;
use stdClass;
use Throwable;
use function get_class;
use function is_array;
use function is_bool;
use function is_object;
use function json_encode;
use function md5;
use function spl_object_hash;
use function sprintf;
use function strpos;
use function substr;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

/**
 * @final
 */
class ConsoleOutputWriter
{
    public const COLOR_NOTICE = '0;97';
    public const COLOR_WARNING = '0;93';
    public const COLOR_ERROR = '0;31';
    public const COLOR_SUCCESS = '0;92';
    private const TRUNCATED_VALUE_MAX_LENGTH = 200;


    public static function outputMessage(string $text, string $color = self::COLOR_NOTICE): void
    {
        echo sprintf("\033[%sm%s\033[0m" . PHP_EOL, $color, $text);
    }


    /**
     * @param mixed $actualValue
     */
    public static function outputSuccessfulMatch(int $argumentIndex, $actualValue): void
    {
        self::outputMessage(
            sprintf(
                '  Parameter #%d: actual value "%s" matches expected value.',
                $argumentIndex,
                self::parameterValueToString($actualValue),
            ),
            self::COLOR_SUCCESS,
        );
    }


    /**
     * @param mixed $expectedValue
     * @param mixed $actualValue
     */
    public static function outputParameterMismatch(int $argumentIndex, $expectedValue, $actualValue): void
    {
        self::outputMessage(
            sprintf(
                "  Parameter #%d: MISMATCH!\n    Expected value: \"%s\"\n    Actual value:   \"%s\"",
                $argumentIndex,
                self::parameterValueToString($expectedValue),
                self::parameterValueToString($actualValue),
            ),
            self::COLOR_ERROR,
        );
    }


    public static function outputArgumentCountMismatch(int $expectedValuesCount, int $actualValuesCount): void
    {
        self::outputMessage(
            sprintf(
                '  MISMATCH! Expected argument count "%s" does not match actual argument count "%s"!' . PHP_EOL,
                $expectedValuesCount,
                $actualValuesCount,
            ),
            self::COLOR_ERROR,
        );
    }


    /**
     * @param mixed $value
     */
    private static function parameterValueToString($value): string
    {
        if (is_array($value) || $value instanceof stdClass) {
            try {
                return Strings::truncate(
                    json_encode($value, JSON_THROW_ON_ERROR),
                    self::TRUNCATED_VALUE_MAX_LENGTH,
                );
            } catch (Throwable $e) {
                return '*unJSONable object*';
            }
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Handling of a Mockery object
        if (is_object($value) && strpos(get_class($value), 'Mockery_') === 0) {
            return get_class($value);
        }

        try {
            /** @throws Throwable */
            $valueAsString = (string)$value;
        } catch (Throwable $exception) {
            $valueAsString = sprintf('%s (#%s)', get_class($value), self::getHashFromObject($value));
        }

        return $valueAsString;
    }


    /**
     * @param mixed $value
     */
    private static function getHashFromObject($value): string
    {
        return substr(md5(spl_object_hash($value)), 0, 8);
    }
}
