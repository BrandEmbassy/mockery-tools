<?php declare(strict_types = 1);

namespace BE\MockeryTools;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

class ArraySubset extends Constraint
{
    /**
     * @var iterable
     */
    private $subset;


    public function __construct(iterable $subset)
    {
        $this->subset = $subset;
    }


    /**
     * @param iterable $other
     *
     * @return bool|void
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = \array_replace_recursive($other, $this->subset);

        $result = $other === $patched;

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }
    }


    public function toString(): string
    {
        return 'has the subset ' . $this->exporter()->export($this->subset);
    }


    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }


    private function toArray(iterable $other): array
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array)$other;
    }
}
