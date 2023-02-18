<?php

namespace App\Tests;

use App\Entity\Model;
use PHPUnit\Framework\TestCase;

class AnyTest extends TestCase
{
	public function testAnything()
	{
		self::assertSame(true, is_a(Model::class, Model::class, true));
	}
}