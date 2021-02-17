<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Exception;

use Closure;
use Nette\StaticClass;
use PHPUnit\Framework\TestCase;
use Throwable;
use function get_class;
use function sprintf;
use function strpos;

final class ExceptionAssertions
{
    use StaticClass;


    /**
     * @param class-string $expectedExceptionClassName
     */
    public static function assertExceptionCallback(
        string $expectedExceptionClassName,
        Closure $expectation,
        Closure $callback,
        ?string $expectedExceptionMessage = null,
        ?int $expectedExceptionCode = null
    ): void {
        try {
            $callback();
        } catch (Throwable $exception) {
            if (!($exception instanceof $expectedExceptionClassName)) {
                TestCase::fail(
                    sprintf(
                        'Exception "%s" expected, but given "%s" with message: "%s".',
                        $expectedExceptionClassName,
                        get_class($exception),
                        $exception->getMessage()
                    )
                );
            }
            if ($expectedExceptionMessage !== null
                && strpos($exception->getMessage(), $expectedExceptionMessage) !== false
            ) {
                TestCase::fail(
                    sprintf(
                        'Exception message "%s" does not contain expected "%s"',
                        $exception->getMessage(),
                        $expectedExceptionMessage
                    )
                );
            }
            if ($expectedExceptionCode !== null && $exception->getCode() !== $expectedExceptionCode) {
                TestCase::fail(
                    sprintf(
                        'Exception code %d is different from expected code %d.',
                        $exception->getCode(),
                        $expectedExceptionCode
                    )
                );
            }

            $expectation($exception);

            return;
        }
        TestCase::fail(sprintf('Exception "%s" expected, none thrown.', $expectedExceptionClassName));
    }
}
