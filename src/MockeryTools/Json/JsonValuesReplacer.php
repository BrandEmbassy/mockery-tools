<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Json;

use Nette\StaticClass;
use Nette\Utils\Json;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function sprintf;
use function str_replace;

/**
 * @final
 */
class JsonValuesReplacer
{
    use StaticClass;


    /**
     * @param array<string, mixed> $valuesToReplace
     */
    public static function replace(array $valuesToReplace, string $jsonString): string
    {
        $keys = [];
        $values = [];
        foreach ($valuesToReplace as $key => $value) {
            if (is_array($value)) {
                $keys[] = sprintf('"%s"', self::decorateReplacementKey($key));
                $values[] = Json::encode($value);

                continue;
            }

            if (is_int($value) || is_float($value)) {
                $keys[] = self::decorateReplacementKey($key, 'string');
                $values[] = (string)$value;
                $keys[] = sprintf('"%s"', self::decorateReplacementKey($key));
                $values[] = (string)$value;

                continue;
            }

            if (is_bool($value)) {
                $keys[] = sprintf('"%s"', self::decorateReplacementKey($key));
                $values[] = $value ? 'true' : 'false';

                continue;
            }

            $keys[] = sprintf('"%s"', self::decorateReplacementKey($key, 'int'));
            $values[] = $value === null ? 'null' : (string)(int)$value;
            $keys[] = sprintf('"%s"', self::decorateReplacementKey($key, 'float'));
            $values[] = $value === null ? 'null' : (string)(float)$value;
            $keys[] = sprintf('"%s"', self::decorateReplacementKey($key, 'bool'));
            $values[] = (bool)$value ? 'true' : 'false';

            if ($value === null) {
                $keys[] = sprintf('"%s"', self::decorateReplacementKey($key, 'string'));
                $values[] = 'null';
                $keys[] = sprintf('"%s"', self::decorateReplacementKey($key));
                $values[] = 'null';

                continue;
            }

            $keys[] = self::decorateReplacementKey($key);
            $values[] = (string)$value;
        }

        return str_replace($keys, $values, $jsonString);
    }


    private static function decorateReplacementKey(string $key, ?string $datatype = null): string
    {
        if ($datatype !== null) {
            $key .= '|' . $datatype;
        }

        return '%' . $key . '%';
    }
}
