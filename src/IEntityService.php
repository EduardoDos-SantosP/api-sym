<?php

namespace App;

interface IEntityService
{
    public static function getModelName(): string;

    public static function findByEntity(string $entityName, string $serviceType, mixed ...$arguments): IEntityService;

    public static function createFromEntity(string $entityName, mixed ...$arguments): IEntityService;
}