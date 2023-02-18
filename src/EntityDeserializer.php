<?php

namespace App;

use App\Controller\EntityController;
use App\Entity\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use function Symfony\Component\String\b;

class EntityDeserializer implements ParamConverterInterface
{
	public function __construct(
		private readonly SerializerInterface $serializer
	) {}
	
	public function supports(ParamConverter $configuration): bool
	{
		return is_a($configuration->getClass(), Model::class, true);
	}
	
	public function apply(Request $request, ParamConverter $configuration): bool
	{
		if ($request->attributes->has('id') || !$request->getContent())
			return false;
		
		$controllerAndAction = $request->get('_controller');
		$controllerClass = (string)b($controllerAndAction)->before('::');
		
		$class = $configuration->getClass();
		$isAbstractParam = $class === Model::class;
		if ($isAbstractParam && !is_a($controllerClass, EntityController::class, true))
			return false;
		
		$class = $isAbstractParam ? $controllerClass::getModelName() : $class;
		
		$obj = $this->deserialize($request, $class);
		$request->attributes->set($configuration->getName(), $obj);
		
		return true;
	}
	
	public function deserialize(Request|string $requestOrJson, string $class = null): object
	{
		$json = is_string($requestOrJson)
			? $requestOrJson
			: $requestOrJson->getContent();
		return $this->serializer->deserialize($json, $class, 'json');
	}
}