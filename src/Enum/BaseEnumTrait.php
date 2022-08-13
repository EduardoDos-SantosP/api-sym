<?php

namespace App\Enum;

use UnitEnum;
use function Symfony\Component\String\b;

trait BaseEnumTrait
{
    public static function getByName(string $name, bool $sensitive = false): ?IEnum
    {
        return $name && static::caseExists($name, $sensitive)
            ? static::${b($name)->lower()->title(true)}
            : null;
    }

    public static function caseExists(string $caseName, bool $sesitive = false): bool
    {
        $sesitiveFunction =
            fn(string $case) => ($sesitive
                ? fn($n) => $n
                : 'ststrtolower')($case);
        return collect(static::cases())
            ->map(fn(UnitEnum $case) => $sesitiveFunction($case->name))
            ->contains($sesitiveFunction($caseName));
    }
}