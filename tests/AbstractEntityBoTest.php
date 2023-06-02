<?php

namespace App\Tests;

use App\Bo\EntityBo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
}