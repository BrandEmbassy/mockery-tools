<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Arrays;

use PHPUnit\Framework\Assert;

final class ArraySubsetAssertions
{
    /**
     * @param mixed[] $expectedArraySubset
     * @param mixed[] $array
     */
    public static function assertArrayContainsSubset(array $expectedArraySubset, array $array): void
    {
        $constraint = new ArraySubsetConstraint($expectedArraySubset);

        Assert::assertThat($array, $constraint);
    }
}
