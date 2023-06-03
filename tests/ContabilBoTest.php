<?php

namespace App\Tests;

use App\Bo\ContabilBo;
use App\Bo\EntityBoInterface;

class ContabilBoTest extends AbstractEntityBoTest
{
	public static function getBoInstance(): EntityBoInterface
	{
		/** @var ContabilBo $bo */
		$bo = self::getContainer()->get(ContabilBo::class)/*::getBo()*/
		;
		return $bo;
	}
}