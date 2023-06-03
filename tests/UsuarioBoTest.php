<?php

namespace App\Tests;

use App\Bo\EntityBoInterface;
use App\Bo\UsuarioBo;

class UsuarioBoTest extends AbstractEntityBoTest
{
	
	public static function getBoInstance(): EntityBoInterface
	{
		return self::getContainer()->get(UsuarioBo::class);
	}
}