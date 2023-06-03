<?php

namespace App\Tests;

use App\Bo\EntityBo;
use App\Entity\Model;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TypeError;
use function Symfony\Component\String\b;

abstract class AbstractEntityBoTest extends KernelTestCase
{
	private static ?EntityBo $bo = null;
	private static array $data = [];
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
		self::$bo = static::getBoInstance();
		self::$data[static::class] = (object)[];
	}
	
	public abstract static function getBoInstance(): EntityBo;
	
	public function testConsulta(): void
	{
		$q = self::getData()->q = self::$bo->all()->count();
		self::assertGreaterThan(0, $q);
	}
	
	protected static function getData(): object
	{
		return self::$data[static::class];
	}
	
	public function testCriarNovo(): void
	{
		$itens = static::$bo->all();
		self::getData()->qtd = $itens->count();
		
		$model = new (static::getModelClass());
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
		
		static::$bo->store($model);
		
		static::assertGreaterThan(0, $model->getId());
		
		static::assertEquals(
			self::getData()->qtd + 1,
			static::$bo->all()->count()
		);
		
		foreach ($model::getProperties() as $prop) {
			if ($prop->type === 'string')
				[$model, $prop->setter]([$model, $prop->getter]() . ' Alterado');
			else if (in_array($prop->type, ['int', 'float']))
				[$model, $prop->setter]([$model, $prop->getter]() + 1);
		}
		static::$bo->store($model);
		
		static::$bo->delete($model);
		
		static::assertEquals(
			self::getData()->qtd,
			static::$bo->all()->count()
		);
	}
	
	private static function getModelClass(): string
	{
		$class = b(static::class)->replace('Tests', 'Entity')->trimSuffix('BoTest');
		if (!class_exists($class))
			throw new RuntimeException('Não foi possível encontrar a classe ' . $class);
		return $class;
	}
}