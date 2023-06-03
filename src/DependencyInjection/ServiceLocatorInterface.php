<?php

namespace App\DependencyInjection;

use App\EntityServiceInterface;
use App\Enum\EnumServiceType;

interface ServiceLocatorInterface
{
	function getService(EnumServiceType $serviceType, string $entity): string;
	
	function getServiceInstance(EnumServiceType $serviceType, string $entity): EntityServiceInterface;
}