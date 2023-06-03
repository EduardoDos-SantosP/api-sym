<?php

namespace App\ServiceLocator;

use App\Enum\EnumServiceType;
use App\IEntityService;

interface ServiceLocatorInterface
{
	function getService(EnumServiceType $serviceType, string $entity): string;
	
	function getServiceInstance(EnumServiceType $serviceType, string $entity): IEntityService;
}