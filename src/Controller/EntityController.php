<?php

namespace App\Controller;

use App\Bo\EntityBo;
use App\Entity\Model;
use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\IEntityService;
use App\ServiceLocator\ServiceLocatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

abstract class EntityController extends Controller implements IEntityService
{
	use EntityServiceTrait;
	
	private readonly EntityBo $bo;
	
	public function __construct(
		SerializerInterface $serializer,
		ServiceLocatorInterface $locator
	) {
		parent::__construct($serializer);
		
		/** @var EntityBo $bo */
		$bo = $locator->getServiceInstance(EnumServiceType::Bo, self::getModelName());
		$this->bo = $bo;
	}
	
	public function getBo(): EntityBo
	{
		return $this->bo;
	}
	
	public function all(): JsonResponse
	{
		return $this->json($this->getBo()->all());
	}
	
	//#[RouteOptions(parameters: ['id'])]
	public function byId(Model $model): JsonResponse
	{
		return $this->json($model);
	}
	
	public function delete(Model $model): JsonResponse
	{
		$this->bo->delete($model);
		return $this->json($model);
	}
	
	protected function deserialize(string|Request $requestOrJson, ?string $class = null): mixed
	{
		return parent::deserialize($requestOrJson, $class ?? self::getModelName());
	}
}