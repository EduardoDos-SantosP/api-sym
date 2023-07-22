<?php

namespace App\Tests;

use App\Entity\Contabil;
use App\Entity\Model;
use App\Entity\Usuario;
use PHPUnit\Framework\TestCase;

class GetPropertiesModelTest extends TestCase
{
	public static function provider(): array
	{
		return [[Contabil::class], [Usuario::class]];
	}
	
	/**
	 * @dataProvider provider
	 * @param class-string<Model> $entity
	 */
	public function test(string $entity): void
	{
		$p = $entity::getProperties();
		self::assertNotEmpty($p);
	}
}