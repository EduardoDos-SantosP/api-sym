<?php

namespace App\Repository;

use App\Entity\Model;
use App\EntityServiceInterface;
use App\EntityServiceTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;
use function Symfony\Component\String\b;

abstract class Repository extends ServiceEntityRepository implements IRepository, EntityServiceInterface
{
	use EntityServiceTrait;
	
	private EntityManagerInterface $em;
	
	public function __construct(ManagerRegistry $registry)
	{
		$entity = $this->getEntityFullName();
		parent::__construct($registry, $entity);
		$this->em = $this->getEntityManager();
	}
	
	private function getEntityFullName(): string
	{
		return b(static::class)
			->replace('Repository', 'Entity')
			->trimSuffix('Entity')
			->toString();
	}
	
	public function all(): Collection
	{
		return collect($this->executeQuery($this->createQueryBuilder('c')));
	}
	
	protected function executeQuery(QueryBuilder $queryBuilder): mixed
	{
		return $queryBuilder->getQuery()->execute();
	}
	
	public function byId(int $id): ?Model
	{
		return $this->find($id);
	}
	
	public function store(Model $model): void
	{
		$this->em->persist($model);
		$this->em->flush($model);
	}
	
	public function delete(Model $model): void
	{
		$this->em->remove($model);
		$this->em->flush($model);
	}
}