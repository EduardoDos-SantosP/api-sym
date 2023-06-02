<?php

namespace App\Tests;

use App\Bo\EntityBo;
use App\Bo\UsuarioBo;

class UsuarioBoTest extends AbstractEntityBoTest
{
	
	public static function getBoInstance(): EntityBo
	{
		return self::getContainer()->get(UsuarioBo::class);
	}
}