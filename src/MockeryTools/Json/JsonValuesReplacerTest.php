<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Json;

use Nette\Utils\FileSystem;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @final
 */
class JsonValuesReplacerTest extends TestCase
{
    public function testLoadsJsonWithMixedTypesAndCorrectlyReplaces(): void
    {
        $inputJson = FileSystem::read(__DIR__ . '/__fixtures__/inputMixedTypes.json');
        $resultJson = JsonValuesReplacer::replace(
            [
                'intId' => 5,
                'floatId' => 6.78,
                'stringId' => '8.1',
                'boolVal' => false,
                'nullValue' => null,
                'arrayField' => [
                    'field1' => 'Lipsum',
                    'field2' => [
                        'subField1' => 5,
                        'subField2' => '',
                        'subField3' => null,
                        'subField4' => false,
                    ],
                ],
            ],
            $inputJson,
        );

        Assert::assertJsonStringEqualsJsonFile(
            __DIR__ . '/__fixtures__/outputMixedTypes.json',
            $resultJson,
        );
    }
}
