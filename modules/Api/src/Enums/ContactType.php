<?php

namespace Modules\Api\Enums;

enum ContactType: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Other = 'other';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
