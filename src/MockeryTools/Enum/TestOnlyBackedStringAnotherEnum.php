<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Enum;

enum TestOnlyBackedStringAnotherEnum: string
{
    case STRING_VALUE = 'string-value';
    case INTEGER_AS_STRING_VALUE = '551';
}
