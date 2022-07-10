<?php

namespace App\Facade;

use App\Entity\Model;
use App\EntityServiceTrait;
use App\Helper\Singleton;
use App\IEntityService;
use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;

abstract class EntityFacade implements IEntityService, IFacade
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

    protected static function getRepository(): Repository
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Singleton::getInstance(
            'facade_repository',
            fn() => self::findByEntity(self::getModelName(), 'repository', self::$manager)
        );
    }

    public function all(): array
    {
        return self::getRepository()->all();
    }
}