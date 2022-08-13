<?php

namespace App\Enum;

interface IEnum
{
    public static function getByName(string $name): ?IEnum;
}