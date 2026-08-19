<?php

namespace App\Support;

use InvalidArgumentException;

class RoadmapType
{
    public const SAAS = 'saas';

    public const INTERNO = 'interno';

    public const BPO = 'bpo';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::SAAS, self::INTERNO, self::BPO];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }

    public static function resolve(?string $type): string
    {
        $type = $type ?? self::SAAS;

        if (!self::isValid($type)) {
            throw new InvalidArgumentException('Tipo de roadmap inválido. Use: saas, interno ou bpo.');
        }

        return $type;
    }
}
