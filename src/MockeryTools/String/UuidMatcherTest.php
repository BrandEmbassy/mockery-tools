<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\String;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class UuidMatcherTest extends TestCase
{
    /**
     * @dataProvider uuidDataProvider
     */
    public function testMatching(string $actualUuid, string $expectedUuid, bool $expectedResult): void
    {
        $uuidMatcher = new UuidMatcher($expectedUuid);
        $actualUuidObject = Uuid::fromString($actualUuid);

        Assert::assertSame($expectedResult, $uuidMatcher->match($actualUuidObject));
    }


    /**
     * @return string[][]|bool[][]
     */
    public function uuidDataProvider(): array
    {
        return [
            'matching' => [
                'actualUuid' => '386502a2-2588-429f-8b50-61711732699b',
                'expectedUuid' => '386502a2-2588-429f-8b50-61711732699b',
                'expectedResult' => true,
            ],
            'not matching' => [
                'actualUuid' => '4563dcb7-041c-4eba-b839-0618e19b8984',
                'expectedUuid' => '386502a2-2588-429f-8b50-61711732699b',
                'expectedResult' => false,
            ],
        ];
    }
}
