<?php

namespace App\Bo;

use App\Contract\ISearcher;
use App\Contract\IStorer;
use App\Entity\Model;
use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\Helper\Singleton;
use App\IEntityService;
use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;

abstract class EntityBo implements IEntityService, IBo, ISearcher, IStorer
{
	use EntityServiceTrait;
	
	private static ?Repository $repository = null;
	protected static ManagerRegistry $manager;
	
	public function __construct(ManagerRegistry $manager)
	{
		self::$manager = $manager;
	}
	
	protected static function getManager(): ManagerRegistry
	{
		return self::$manager;
	}
	
	/** @param class-string $entityName */
	protected static function createNew(ManagerRegistry $manager, string $entityName): EntityBo
	{
		/** @var $service EntityBo */
		$service = self::createFromEntity($entityName, $manager);
		return $service;
	}
	
	public function all(): Collection
	{
		return self::getRepository()->all();
	}
	
	protected static function getRepository(): Repository
	{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return Singleton::getInstance(
			'bo_repository',
			fn() => self::findByEntity(self::getModelName(), EnumServiceType::Repository, self::$manager)
		);
	}
	
	public function byId(int $id): ?Model
	{
		return self::getRepository()->byId($id);
	}
	
	public function store(Model $model): void
	{
		self::getRepository()->store($model);
	}
	
	public function delete(Model $model): void
	{
		self::getRepository()->delete($model);
	}
}