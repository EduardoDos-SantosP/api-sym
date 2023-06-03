<?php

namespace App\DependencyInjection;

use App\Entity\Model;
use App\EntityServiceInterface;
use App\Enum\EnumServiceType;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use function Symfony\Component\String\b;

class ServiceManager implements ServiceLocatorInterface
{
	public function __construct(
		private readonly ManagerRegistry $managerRegistry,
		public readonly ?ContainerInterface $container// = null
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
	
	public function getServiceInstance(EnumServiceType $serviceType, string $entity): EntityServiceInterface
	{
		$service = $this->getService($serviceType, $entity);
		try {
			/** @var EntityServiceInterface $instance */
			$instance = $this->container->get($service);
			return $instance;
		} catch (ServiceNotFoundException $e) {
			throw new RuntimeException("O serviço '$service' não é acessível pelo container");
		}
	}
	
	/** @param class-string<Model> $modelName */
	public function createBoFactory(string $modelName): ServiceFactoryInterface
	{
		return new ServiceFactory($modelName, [$this->createRepositoryFactory($modelName)->create()]);
	}
	
	/** @param class-string<Model> $modelName */
	public function createRepositoryFactory(string $modelName): ServiceFactoryInterface
	{
		return new ServiceFactory($modelName, [$this->managerRegistry]);
	}
}