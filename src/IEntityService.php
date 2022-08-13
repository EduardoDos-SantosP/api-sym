<?php

namespace App;

use App\Enum\EnumServiceType;

interface IEntityService
{
    public static function getModelName(): string;

    public static function findByEntity(string $entityName, EnumServiceType $serviceType, mixed ...$arguments): IEntityService;

    public static function createFromEntity(string $entityName, mixed ...$arguments): IEntityService;
}