<?php

namespace App\Tests;

use App\Entity\Movimentacao;
use App\Entity\Model;
use App\Entity\MovimentacaoItem;
use App\Entity\Usuario;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractCrudTest extends WebTestCase
{
	private static array $publicData = [];
	protected static array $testEntities = [];
	/** @var class-string<Model> $currentEntity */
	protected static string $currentEntity;
	private static int $currentEntityIndex = 0;
	
	protected static KernelBrowser $client;
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::$client = self::createClient();
		self::$publicData[static::class] = (object)[];
        self::bootEntities();
		dump(static::class);
	}

    protected function setUp(): void
    {
        $this->updateCurrentEntity();
        dump(self::$currentEntity);
    }

    protected static function data(): object
	{
		return self::$publicData[static::class];
	}
	
	protected static function bootEntities(): void
	{
        if (isset(self::$testEntities) && count(self::$testEntities))
            return;
		self::$testEntities = [
			/*Usuario::class,
			Movimentacao::class*/
		];
		self::addInheritedTest();
	}
	
	private static function addInheritedTest(): void
	{
		if (static::class === self::class) return;
		if (!($entity = self::addionalEntityToTest())) return;
		if (in_array($entity, self::$testEntities)) return;
		self::$testEntities[] = $entity;
	}
	
	/** @return class-string<Model>|null */
	protected static function addionalEntityToTest(): ?string
	{
		return null;
	}
	
	private function entitiesProvider(): iterable
	{
		self::bootEntities();
		return collect(self::$testEntities)->map(fn() => []);
	}
	
	protected function updateCurrentEntity(): void
	{
		$currentEntity = self::$testEntities[self::$currentEntityIndex++] ?? null;
        if (!$currentEntity) self::markTestSkipped('No entities to test');
        self::$currentEntity = $currentEntity;
	}
	
	/** @dataProvider entitiesProvider */
	public function testCrud(): void
	{
		self::data()->qtd = $this->fetchCountEntities();
		
		$id = $this->store();
		
		static::assertEquals(
			self::data()->qtd + 1,
			$this->fetchCountEntities()
		);
		
		$model = $this->fetchById($id);
		
		$this->delete($model);
		
		static::assertEquals(
			self::data()->qtd,
			$this->fetchCountEntities()
		);
	}
	
	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();
		self::$currentEntityIndex = 0;
	}
	
	public abstract function fetchCountEntities(): int;
	
	public abstract function store(): int;
	
	public abstract function fetchById(int $id): object;
	
	public abstract function delete(object $model): void;
}