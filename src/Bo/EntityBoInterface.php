<?php

namespace App\Bo;

use App\Contract\ISearcher;
use App\Contract\IStorer;
use App\DependencyInjection\ServiceLocatorInterface;
use App\Entity\Model;
use App\EntityServiceInterface;
use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;

abstract class EntityBoInterface implements EntityServiceInterface, IBo, ISearcher, IStorer
{
	use EntityServiceTrait;
	
	public function __construct(
		private readonly Repository $repository
	) {}
	
	public static function createBo(ServiceLocatorInterface $locator, ManagerRegistry $manager): static
	{
		/** @var class-string<Repository> $repositotyClass */
		$repositotyClass = $locator->getService(EnumServiceType::Repository, self::getModelName());
		$repositoty = new $repositotyClass($manager);
		return new static($repositoty);
	}
	
	public function all(): Collection
	{
		return $this->getRepository()->all();
	}
	
	protected function getRepository(): Repository
	{
		return $this->repository;
	}
	
	public function byId(int $id): ?Model
	{
		return $this->getRepository()->byId($id);
	}
	
	public function store(Model $model): void
	{
		$this->getRepository()->store($model);
	}
	
	public function delete(Model $model): void
	{
		$this->getRepository()->delete($model);
	}
}