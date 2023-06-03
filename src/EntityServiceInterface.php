<?php

namespace App;

use App\Enum\EnumServiceType;

interface EntityServiceInterface
{
	public static function getModelName(): string;
	
	public static function findByEntity(
		string $entityName,
		EnumServiceType $serviceType,
		mixed ...$arguments
	): EntityServiceInterface;
	
	public static function createFromEntity(string $entityName, mixed ...$arguments): EntityServiceInterface;
}