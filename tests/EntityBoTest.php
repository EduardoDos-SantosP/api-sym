<?php

namespace App\Tests;

use App\Bo\EntityBo;
use App\Entity\Model;
use App\Enum\EnumServiceType;
use App\ServiceLocator\ServiceLocatorInterface;
use TypeError;

class EntityBoTest extends AbstractCrudTest
{
	private ?EntityBo $bo = null;
	
	public static function getBoInstance(): EntityBo
	{
		/** @var ServiceLocatorInterface $locator */
		$locator = self::getContainer()->get(ServiceLocatorInterface::class);
		/** @var EntityBo $bo */
		$bo = $locator->getServiceInstance(EnumServiceType::Bo, self::$currentEntity);
		
		return $bo;
	}
	
	protected function updateCurrentEntity(): void
	{
		parent::updateCurrentEntity();
		$this->bo = self::getBoInstance();
	}
	
	public function fetchCountEntities(): int
	{
		return $this->bo->all()->count();
	}
	
	public function store(): int
	{
		$model = new (static::$currentEntity);
		static::assertInstanceOf(Model::class, $model);
		
		foreach ($model::getProperties() as $prop) {
			if (!$prop->setter) continue;
			
			$testValue = match ($prop->type) {
				'int', 'float' => 10,
				'string' => 'Valor teste para ' . $prop->property,
				'bool' => false,
				'array' => [],
				default => null
			};
			
			try {
				[$model, $prop->setter]($testValue);
			} catch (TypeError) {
			}
		}
		$this->bo->store($model);
		
		static::assertGreaterThan(0, $model->getId());
		
		foreach ($model::getProperties() as $prop) {
			if ($prop->type === 'string')
				[$model, $prop->setter]([$model, $prop->getter]() . ' Alterado');
			else if (in_array($prop->type, ['int', 'float']))
				[$model, $prop->setter]([$model, $prop->getter]() + 1);
		}
		
		$this->bo->store($model);
		
		static::assertGreaterThan(0, $model->getId());
		
		return $model->getId();
	}
	
	public function fetchById(int $id): object
	{
		return $this->bo->byId($id);
	}
	
	public function delete(object $model): void
	{
		self::assertInstanceOf(self::$currentEntity, $model);
		/** @var Model $model */
		$this->bo->delete($model);
	}
}