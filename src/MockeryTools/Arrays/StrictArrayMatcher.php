<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Arrays;

use Mockery\Matcher\MatcherAbstract;
use function is_array;
use function ksort;
use function print_r;
use const PHP_EOL;

class StrictArrayMatcher extends MatcherAbstract
{
    /**
     * @var mixed[]
     */
    private array $sortedArray;


    /**
     * @param mixed[] $array
     */
    public function __construct(array $array)
    {
        parent::__construct();

        $this->sortedArray = $this->sortArray($array);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     *
     * @param mixed $actual
     */
    public function match(&$actual): bool
    {
        if (!is_array($actual)) {
            return false;
        }

        return $this->sortArray($actual) === $this->sortedArray;
    }


    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    private function sortArray(array $array): array
    {
        ksort($array);
        foreach ($array as &$item) {
            if (is_array($item)) {
                $item = $this->sortArray($item);
            }
        }

        return $array;
    }


    public function __toString(): string
    {
        $output = '<ArrayMatch[Array: ' . PHP_EOL;

        $output .= print_r($this->sortedArray, true);

        return $output . ']>';
    }
}
