<?php

namespace App\Tests;

use App\Bo\ContabilBo;
use App\Bo\EntityBo;
use App\Controller\ContabilController;

class ContabilBoTest extends AbstractEntityBoTest
{
	public static function getBoInstance(): EntityBo
	{
		/** @var ContabilBo $bo */
		$bo = self::getContainer()->get(ContabilController::class)::getBo();
		return $bo;
	}
}