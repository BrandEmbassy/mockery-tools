<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Arrays;

use ArrayObject;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;
use function array_replace_recursive;
use function is_array;
use function var_export;

final class ArraySubsetConstraint extends Constraint
{
    /**
     * @var iterable|mixed[]
     */
    private $subset;


    /**
     * @param iterable|mixed[] $subset
     */
    public function __construct(iterable $subset)
    {
        $this->subset = $subset;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param mixed $other
     *
     * @return boolean|void
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = array_replace_recursive($other, $this->subset);

        $result = $other === $patched;

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                var_export($patched, true),
                var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }
    }


    public function toString(): string
    {
        return 'has the subset ' . $this->exporter()->export($this->subset);
    }


    /**
     * @param mixed $other
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }


    /**
     * @param iterable|mixed[] $other
     *
     * @return mixed[]
     */
    private function toArray(iterable $other): array
    {
        if (is_array($other)) {
            return $other;
        }

        if ($other instanceof ArrayObject) {
            return $other->getArrayCopy();
        }

        // Keep BC even if we know that array would not be the expected one
        return (array)$other;
    }
}
