<?php

namespace App\Repository;

use App\Entity\Model;
use App\EntityServiceTrait;
use App\IEntityService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\String\b;

abstract class Repository extends ServiceEntityRepository implements IRepository, IEntityService
{
    use EntityServiceTrait;

    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry)
    {
        $entity = $this->getEntityFullName();
        parent::__construct($registry, $entity);
        $this->em = $this->getEntityManager();
    }

    public function all(): array
    {
        return $this->executeQuery($this->createQueryBuilder('c'));
    }

    public function byId(int $id): Model
    {
        return $this->find($id);
    }

    public function store(Model $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush($entity);
    }

    public function delete(Model $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush($entity);
    }

    protected function executeQuery(QueryBuilder $queryBuilder): mixed
    {
        return $queryBuilder->getQuery()->execute();
    }

    private function getEntityFullName(): string
    {
        return b(static::class)
            ->replace('Repository', 'Entity')
            ->trimSuffix('Entity')
            ->toString();
    }
}