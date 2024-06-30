<?php

namespace App\ServiceLocator;

use App\Entity\Model;
use App\Enum\EnumServiceType;
use App\IEntityService;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\String\b;

class ServiceLocator implements ServiceLocatorInterface
{
	public function __construct(
		private readonly ContainerInterface $container
	) {}
	
	public function getService(EnumServiceType $serviceType, string $entity): string
	{
		if (!is_a($entity, Model::class, true))
			throw new InvalidArgumentException("A entidade '$entity' não é reconhecida");
		
		$service = b($entity)->trimSuffix('Entity')
			->replace('\\Entity\\', "\\$serviceType->name\\")
			->append($serviceType->name);
		
		if (!class_exists($service, true))
			throw new RuntimeException("Não foi possível localizar o serviço pois '$service' não existe");
		return $service;
	}
	
	public function getServiceInstance(EnumServiceType $serviceType, string $entity): IEntityService
	{
		$service = $this->getService($serviceType, $entity);
		try {
			/** @var IEntityService $instance */
			$instance = $this->container->get($service);
			return $instance;
		} catch (ServiceNotFoundException $e) {
			throw new RuntimeException("O serviço '$service' não é acessível pelo container");
		}
	}
}