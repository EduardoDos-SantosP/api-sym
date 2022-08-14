<?php

namespace App\Facade;

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

abstract class EntityFacade implements IEntityService, IFacade, ISearcher, IStorer
{
    use EntityServiceTrait;

    private static ManagerRegistry $manager;
    private static ?Repository $repository = null;

    public function __construct(ManagerRegistry $manager)
    {
        self::$manager = $manager;
    }

    /** @param class-string $entityName */
    protected static function createNew(ManagerRegistry $manager, string $entityName): EntityFacade
    {
        /** @var $service EntityFacade */
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
            'facade_repository',
            fn() => self::findByEntity(self::getModelName(), EnumServiceType::Repository, self::$manager)
        );
    }

    public function store(Model $model): void
    {
        self::getRepository()->store($model);
    }

    public function delete(Model $model): void
    {
        self::getRepository()->delete($model);
    }

    public function byId(int $id): ?Model
    {
        return self::getRepository()->byId($id);
    }
}