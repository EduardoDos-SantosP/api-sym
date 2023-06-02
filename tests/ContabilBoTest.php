<?php

namespace App\Tests;

use App\Bo\ContabilBo;
use App\Bo\EntityBo;

class ContabilBoTest extends AbstractEntityBoTest
{
	public static function getBoInstance(): EntityBo
	{
		/** @var ContabilBo $bo */
		$bo = self::getContainer()->get(ContabilBo::class)/*::getBo()*/
		;
		return $bo;
	}
}