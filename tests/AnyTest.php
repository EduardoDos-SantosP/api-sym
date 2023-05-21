<?php

namespace App\Tests;

use App\Controller\ContabilController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnyTest extends KernelTestCase
{
	private static int $length;

//	public function __construct(?string $name = null, array $data = [], $dataName = '', ?ContabilBo $bo = null)
//	{
//		dump($bo);
//		parent::__construct(
//			$name,
//			$data,
//			$dataName
//		);
//	}
	
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		self::bootKernel();
	}
	
	public static function fornecedorDeControllers(): array
	{
		return [
			[self::getContainer()->get(ContabilController::class)]
		];
	}

//	/**
//	 * @dataProvider fornecedorDeControllers
//	 */
//	public function testConsulta1(ContabilController $controller): void
//	{
//		$r = json_decode($controller->all()->getContent());
//		dump(__METHOD__, $r);
//		self::$length = count($r);
//		self::assertIsArray($r);
//	}
//
//	/**
//	 * @dataProvider fornecedorDeControllers
//	 */
//	public function testCriarNovo(ContabilController $controller): void
//	{
//		$model = new Contabil();
//		$model->setNome('T1');
//		$model->setDescricao('Teste 1');
//		$model->setValor(1);
//		$model->setData(new DateTimeLocal());
//
//		$r = $controller->new($model);
//		dump(__METHOD__, $r->getContent());
//		self::assertEquals(200, $r->getStatusCode());
//	}
//
//	/**
//	 * @dataProvider fornecedorDeControllers
//	 */
//	public function testConsulta2(ContabilController $controller): void
//	{
//		$r = json_decode($controller->all()->getContent());
//		dump(__METHOD__, $r);
//		self::assertGreaterThan(self::$length, count($r));
//	}
}