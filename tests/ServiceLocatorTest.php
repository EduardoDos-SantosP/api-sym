<?php

namespace App\Tests;

use App\DependencyInjection\ServiceLocatorInterface;
use App\Entity\Contabil;
use App\Entity\Usuario;
use App\Enum\EnumServiceType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceLocatorTest extends KernelTestCase
{
	private static ServiceLocatorInterface $locator;
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
		self::$locator = self::getContainer()->get(ServiceLocatorInterface::class);
	}
	
	public static function provider(): iterable
	{
		foreach (EnumServiceType::cases() as $case)
			foreach ([Usuario::class, Contabil::class] as $entity)
				yield [$case, $entity];
	}
	
	/** @dataProvider provider */
	public function testServiceMatched(EnumServiceType $serviceType, string $entity): void
	{
		$s = self::$locator->getService($serviceType, $entity);
		//echo PHP_EOL . $s . PHP_EOL;
		self::assertNotEmpty($s);
	}
}