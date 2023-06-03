<?php

namespace App\Controller;

use App\Bo\EntityBoInterface;
use App\DependencyInjection\ServiceLocatorInterface;
use App\EntityServiceInterface;
use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

abstract class EntityControllerInterface extends Controller implements EntityServiceInterface
{
	use EntityServiceTrait;
	
	private readonly EntityBoInterface $bo;
	
	public function __construct(
		SerializerInterface $serializer,
		ServiceLocatorInterface $locator
	) {
		parent::__construct($serializer);
		
		/** @var EntityBoInterface $bo */
		$bo = $locator->getServiceInstance(EnumServiceType::Bo, self::getModelName());
		$this->bo = $bo;
	}
	
	public function getBo(): EntityBoInterface
	{
		return $this->bo;
	}
	
	protected function deserialize(string|Request $requestOrJson, ?string $class = null): mixed
	{
		return parent::deserialize($requestOrJson, $class ?? self::getModelName());
	}
}