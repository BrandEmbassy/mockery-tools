<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\MockeryVerbose;

use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;

/**
 * @final
 */
class MockeryVerbose extends Mockery
{
    /**
     * @param mixed $args
     *
     * @return MockInterface|LegacyMockInterface
     */
    public static function mock(...$args)
    {
        $mock = parent::mock(...$args);

        $mockReflection = new ReflectionClass($mock);
        try {
            $propertyReceivedMethodCalls = $mockReflection->getProperty('_mockery_receivedMethodCalls');
        } catch (ReflectionException $e) {
            echo $e->getMessage();

            return $mock;
        }

        $propertyReceivedMethodCalls->setAccessible(true);
        $propertyReceivedMethodCalls->setValue($mock, new ReceivedMethodCallsVerbose($mock));

        return $mock;
    }
}
