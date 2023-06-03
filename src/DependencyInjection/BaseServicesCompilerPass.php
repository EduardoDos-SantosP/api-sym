<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BaseServicesCompilerPass implements CompilerPassInterface
{
	/**
	 * @inheritDoc
	 */
	public function process(ContainerBuilder $container): void
	{
//		$def = $container->getDefinition(ManagerRegistry::class);
//		$def->setFactory()
//		dump($def);
		
		self::processBos($container);
		self::processControllers($container);
	}
	
	private static function processBos(ContainerBuilder $container): void
	{
		foreach ($container->findTaggedServiceIds('app.bo') as $serviceId => $tags) {
//			$def = $container->getDefinition($serviceId);
//
//			/** @var EntityServiceInterface $service */
//			$service = $def->getClass();
//
//			/** @var ServiceManager $manager */
//			$manager = $container->get(ServiceManager::class);
//
//			$factory = $manager->createBoFactory($service::getModelName());
//
//			$def->setFactory(self::createFactoryReference($factory));
		}
	}
	
	private static function processControllers(ContainerBuilder $container): void
	{
		foreach ($container->findTaggedServiceIds('app.controller') as $serviceId => $tags) {
		
		}
	}
	
	private static function createFactoryReference(ServiceFactoryInterface $factory): array
	{
		return [$factory, 'create'];
	}
}