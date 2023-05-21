<?php

namespace App\Tests;

use App\Bo\EntityBo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractEntityBoTest extends KernelTestCase
{
	private static ?EntityBo $bo = null;
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
		self::$bo = static::getBoInstance();
	}
	
	public abstract static function getBoInstance(): EntityBo;
	
	public function testNotNull()
	{
		self::assertNotNull(self::$bo);
	}
}