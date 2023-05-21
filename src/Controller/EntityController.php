<?php

namespace App\Controller;

use App\Bo\EntityBo;
use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\IEntityService;
use App\Util\Singleton;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

abstract class EntityController extends Controller implements IEntityService
{
	use EntityServiceTrait;
	
	private static ManagerRegistry $manager;
	private static ?EntityBo $bo = null;
	
	public function __construct(ManagerRegistry $manager, SerializerInterface $serializer)
	{
		parent::__construct($serializer);
		self::$manager = $manager;
	}
	
	public static function getManager(): ManagerRegistry
	{
		return self::$manager;
	}
	
	public static function getBo(): EntityBo
	{
		/** @var EntityBo $service */
		$service = Singleton::getInstance(
			'controller_bo',
			fn() => self::findByEntity(self::getModelName(), EnumServiceType::Bo, self::$manager)
		);
		return $service;
	}
	
	protected function deserialize(string|Request $requestOrJson, ?string $class = null): mixed
	{
		return parent::deserialize($requestOrJson, $class ?? self::getModelName());
	}
}