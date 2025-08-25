<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TypeError;

/**
 * @final
 */
class UuidMockeryMatcherFactoryTest extends TestCase
{
    private const DOES_MATCH = true;

    private const DOES_NOT_MATCH = false;

    private const EXPECTED_UUID_STRING = '072175e1-e39d-492e-8274-ec5a8184d8d5';


    /**
     * @dataProvider uuidMatchesProvider
     */
    public function testCreatedMatcherMatchesUuid(
        bool $expectedMatch,
        string $expectedUuidString,
        UuidInterface $uuid
    ): void {
        $matcher = UuidMockeryMatcherFactory::create($expectedUuidString);

        $match = $matcher->match($uuid);

        Assert::assertSame($expectedMatch, $match);
    }


    /**
     * @return mixed[][]
     */
    public static function uuidMatchesProvider(): array
    {
        $inputUuid1 = Uuid::fromString(self::EXPECTED_UUID_STRING);
        $inputUuid2 = Uuid::fromString('7f8b8912-a10d-4c77-9b92-6323ffa405d2');

        return [
            'Not UUID expectation passes' => [
                'expectedMatch' => self::DOES_NOT_MATCH,
                'expectedUuidString' => 'qwerty.....',
                'uuid' => $inputUuid1,
            ],
            'UUID does not match' => [
                'expectedMatch' => self::DOES_NOT_MATCH,
                'expectedUuidString' => self::EXPECTED_UUID_STRING,
                'uuid' => $inputUuid2,
            ],
            'UUID matches' => [
                'expectedMatch' => self::DOES_MATCH,
                'expectedUuidString' => self::EXPECTED_UUID_STRING,
                'uuid' => $inputUuid1,
            ],
        ];
    }


    public function testFailsWhenNotUuidPassed(): void
    {
        $matcher = UuidMockeryMatcherFactory::create(self::EXPECTED_UUID_STRING);
        $stringNotUuidObject = self::EXPECTED_UUID_STRING;

        $this->expectException(TypeError::class);

        $matcher->match($stringNotUuidObject);
    }
}
