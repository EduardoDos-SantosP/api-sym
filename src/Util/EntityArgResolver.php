<?php

namespace App\Util;

use App\Annotation\Routing\EntityArgProvider;
use App\Controller\EntityController;
use App\Entity\Model;
use App\Enum\EnumArgProviderMode;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

class EntityArgResolver implements ArgumentValueResolverInterface
{
	private array $requestBody;
	
	public function __construct(
		private readonly SerializerInterface $serializer,
		private readonly ContainerInterface $container,
		private readonly RouterInterface $router
	) {}
	
	public function supports(Request $request, ArgumentMetadata $argument): bool
	{
		return is_a($argument->getType(), Model::class, true);
	}
	
	public function resolve(Request $request, ArgumentMetadata $argument): iterable
	{
		$argProvider = $argument->getAttributesOfType(EntityArgProvider::class)[0] ?? new EntityArgProvider;
		$class = $argProvider->getClassToDeserialize() ?? $argument->getType();
		
		if ($class === Model::class) {
			/** @var EntityController $controller */
			$controller = $this->getController($request);
			$class = $controller::getModelName();
		}
		
		yield match ($argProvider->getMode()) {
			EnumArgProviderMode::Deserialize => $this->deserialize($request, $class),
			EnumArgProviderMode::Query => $this->query($request, $class),
			EnumArgProviderMode::Merge => $this->merge($request, $class)
		};
	}
	
	private function getController(Request $request): string
	{
		$route = $this->router->match($request);
		[$controller] = explode('::', $route['_controller']);
		return $controller;
	}
	
	private function deserialize(Request $request, string $class): Model
	{
		return $this->serializer->deserialize($request->getContent(), $class, 'json');
	}
	
	private function query(Request $request, string $class): ?Model
	{
		/** @var EntityController $controller */
		$controller = $this->container->get($this->getController($request));
		
		$this->requestBody = json_decode($request->getContent(), true);
		return $controller->getBo()->byId($this->requestBody['id'] ?? 0);
	}
	
	private function merge(Request $request, string $class): Model
	{
		$queried = $this->query($request, $class);
		$deserialized = $this->deserialize($request, $class);
		if (!$queried) return $deserialized;
		
		$reflect = new ReflectionClass($class);
		foreach ($this->requestBody as $key => $_)
			if ($reflect->hasProperty($key)
				&& $reflect->hasMethod($setter = 'set' . ucfirst($key))
				&& $reflect->hasMethod($getter = 'get' . ucfirst($key)))
				$queried->$setter($deserialized->$getter());
		
		return $queried;
	}
}