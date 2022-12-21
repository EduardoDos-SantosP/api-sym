<?php

namespace App;

use App\Entity\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class EntityDeserializer implements ParamConverterInterface
{
	private SerializerInterface $serializer;
	
	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}
	
	public function supports(ParamConverter $configuration): bool
	{
		return is_subclass_of($configuration->getClass(), Model::class);
	}
	
	public function apply(Request $request, ParamConverter $configuration): bool
	{
		if ($request->attributes->has('id') || !$request->getContent())
			return false;
		$obj = $this->deserialize($request, $configuration->getClass());
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