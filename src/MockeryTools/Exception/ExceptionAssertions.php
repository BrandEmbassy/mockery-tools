<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Exception;

use Closure;
use Nette\StaticClass;
use PHPUnit\Framework\TestCase;
use Throwable;
use function get_class;
use function sprintf;

final class ExceptionAssertions
{
    use StaticClass;


    /**
     * @param class-string $expectedExceptionClassName
     */
    public static function assertExceptionCallback(
        string $expectedExceptionClassName,
        Closure $expectation,
        Closure $callback
    ): void {
        try {
            $callback();
        } catch (Throwable $exception) {
            if ($exception instanceof $expectedExceptionClassName) {
                TestCase::fail(
                    sprintf(
                        'Exception "%s" expected, but given "%s" with message: "%s".',
                        $expectedExceptionClassName,
                        get_class($exception),
                        $exception->getMessage()
                    )
                );
            }

            $expectation($exception);

            return;
        }
        TestCase::fail(sprintf('Exception "%s" expected, none thrown.', $expectedExceptionClassName));
    }
}
