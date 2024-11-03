<?php

namespace App\Tests;

use App\Entity\Movimentacao;
use App\Entity\Usuario;
use App\Enum\EnumServiceType;
use App\ServiceLocator\ServiceLocatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceLocatorTest extends KernelTestCase
{
	private static ServiceLocatorInterface $locator;
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
		/** @var ServiceLocatorInterface $locator */
		$locator = self::getContainer()->get(ServiceLocatorInterface::class);
		self::$locator = $locator;
	}
	
	public static function provider(): iterable
	{
		foreach (EnumServiceType::cases() as $case)
			foreach ([Usuario::class, Movimentacao::class] as $entity)
				yield [$case, $entity];
	}
	
	/** @dataProvider provider */
	public function testServiceMatched(EnumServiceType $serviceType, string $entity): void
	{
		$s = self::$locator->getService($serviceType, $entity);
		self::assertNotEmpty($s);
	}
}