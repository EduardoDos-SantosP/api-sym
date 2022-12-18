<?php

namespace App\Controller;

use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\Facade\EntityFacade;
use App\Helper\Singleton;
use App\IEntityService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

abstract class EntityController extends Controller implements IEntityService
{
	use EntityServiceTrait;
	
	private static ManagerRegistry $manager;
	private static ?EntityFacade $facade = null;
	
	public function __construct(ManagerRegistry $manager, SerializerInterface $serializer)
	{
		parent::__construct($serializer);
		self::$manager = $manager;
	}
	
	public static function getManager(): ManagerRegistry
	{
		return self::$manager;
	}
	
	protected static function getFacade(): EntityFacade
	{
		/** @var EntityFacade $service */
		$service = Singleton::getInstance(
			'controller_facade',
			fn() => self::findByEntity(self::getModelName(), EnumServiceType::Facade, self::$manager)
		);
		return $service;
	}
	
	protected function deserialize(string|Request $requestOrJson, ?string $class = null): mixed
	{
		return parent::deserialize($requestOrJson, $class ?? self::getModelName());
	}
}