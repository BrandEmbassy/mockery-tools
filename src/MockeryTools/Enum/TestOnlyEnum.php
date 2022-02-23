<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

use MabeEnum\Enum;

/**
 * @final
 */
class TestOnlyEnum extends Enum
{
    public const STRING_VALUE = 'string-value';
    public const INTEGER_AS_STRING_VALUE = '551';
    public const INTEGER_VALUE = 551;
}
