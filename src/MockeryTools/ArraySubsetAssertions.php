<?php declare(strict_types = 1);

namespace BE\MockeryTools;

trait ArraySubsetAssertions
{
    public static function assertArrayContainsSubset(array $expectedArraySubset, array $array): void{

        $constraint = new ArraySubset($expectedArraySubset);

        self::assertThat($array, $constraint, '');
    }
}
