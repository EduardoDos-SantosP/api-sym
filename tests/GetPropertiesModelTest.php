<?php

namespace App\Tests;

use App\Entity\Contabil;
use App\Entity\Model;
use PHPUnit\Framework\TestCase;

class GetPropertiesModelTest extends TestCase
{
	public static function provider(): array
	{
		return [[Contabil::class]];
	}
	
	/**
	 * @dataProvider provider
	 * @param class-string<Model> $entity
	 */
	public function test(string $entity): void
	{
		$p = $entity::getProperties();
		/*echo PHP_EOL;
		dump($p);
		echo PHP_EOL;*/
		self::assertNotEmpty($p);
	}
}