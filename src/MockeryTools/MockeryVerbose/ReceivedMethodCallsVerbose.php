<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\MockeryVerbose;

use Mockery\Expectation;
use Mockery\Matcher\AnyArgs;
use Mockery\Matcher\MatcherAbstract;
use Mockery\Matcher\NoArgs;
use Mockery\MethodCall;
use Mockery\MockInterface;
use Mockery\ReceivedMethodCalls;
use ReflectionClass;
use function assert;
use function count;
use function get_class;
use function reset;
use function sprintf;
use const PHP_EOL;

/**
 * @final
 */
class ReceivedMethodCallsVerbose extends ReceivedMethodCalls
{
    private MockInterface $mock;


    public function __construct(MockInterface $mock)
    {
        $this->mock = $mock;
    }


    public function push(MethodCall $methodCall): void
    {
        parent::push($methodCall);

        $this->matchMethodCall($methodCall);
    }


    private function matchMethodCall(MethodCall $methodCall): void
    {
        $methodName = $methodCall->getMethod();
        $expectationsFor = $this->mock->mockery_getExpectationsFor($methodName);

        if ($expectationsFor === null) {
            ConsoleOutputWriter::outputMessage(
                sprintf('Method "%s" was not found in expectations.', $methodName),
                ConsoleOutputWriter::COLOR_ERROR,
            );

            return;
        }

        $expectations = $expectationsFor->getExpectations();

        if (count($expectations) === 0) {
            return;
        }

        if (count($expectations) > 1) {
            ConsoleOutputWriter::outputMessage(
                sprintf('Multiple expectations found for "%s", skipping.' . PHP_EOL, $methodName),
                ConsoleOutputWriter::COLOR_WARNING,
            );

            return;
        }

        $expectation = reset($expectations);
        assert($expectation instanceof Expectation);

        $expectedValues = $this->getExpectedValues($expectation);
        $expectedValuesCount = $this->getExpectedValuesCount($expectedValues);

        ConsoleOutputWriter::outputMessage(
            sprintf('Mockery: matching method "%s":', $methodName),
            ConsoleOutputWriter::COLOR_WARNING,
        );

        $actualValues = $methodCall->getArgs();
        $actualValuesCount = count($actualValues);

        if ($expectedValuesCount !== null && ($actualValuesCount !== $expectedValuesCount)) {
            ConsoleOutputWriter::outputArgumentCountMismatch($expectedValuesCount, $actualValuesCount);

            return;
        }

        $this->matchArguments($expectedValues, $actualValues);

        echo PHP_EOL;
    }


    /**
     * @param array<int, mixed> $expectedValues
     * @param array<int, mixed> $actualValues
     */
    private function matchArguments(array $expectedValues, array $actualValues): void
    {
        $argumentIndex = 0;
        while (isset($expectedValues[$argumentIndex], $actualValues[$argumentIndex])) {
            $expectedValue = $expectedValues[$argumentIndex];
            $actualValue = $actualValues[$argumentIndex];
            ++$argumentIndex;

            if ($expectedValue === $actualValue) {
                ConsoleOutputWriter::outputSuccessfulMatch($argumentIndex, $actualValue);
                continue;
            }

            if ($expectedValue instanceof MatcherAbstract && $expectedValue->match($actualValue)) {
                ConsoleOutputWriter::outputSuccessfulMatch($argumentIndex, $actualValue);
                continue;
            }

            if ($expectedValue instanceof MatcherAbstract && !$expectedValue->match($actualValue)) {
                ConsoleOutputWriter::outputParameterMismatch($argumentIndex, get_class($expectedValue), $actualValue);
                continue;
            }

            ConsoleOutputWriter::outputParameterMismatch($argumentIndex, $expectedValue, $actualValue);
        }

        if ($argumentIndex === 0) {
            ConsoleOutputWriter::outputMessage('  No parameters to match.', ConsoleOutputWriter::COLOR_SUCCESS);
        }
    }


    /**
     * @return array<int, mixed>
     */
    private function getExpectedValues(Expectation $expectation): array
    {
        $expectationReflection = new ReflectionClass($expectation);
        $propertyExpectedArgs = $expectationReflection->getProperty('_expectedArgs');
        $propertyExpectedArgs->setAccessible(true);

        return $propertyExpectedArgs->getValue($expectation);
    }


    /**
     * @param array<int, mixed> $expectedValues
     */
    private function getExpectedValuesCount(array $expectedValues): ?int
    {
        $firstExpectedValue = reset($expectedValues);

        if ($firstExpectedValue instanceof AnyArgs) {
            return null;
        }

        if ($firstExpectedValue instanceof NoArgs) {
            return 0;
        }

        return count($expectedValues);
    }
}
