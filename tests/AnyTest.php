<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use function Symfony\Component\String\b;

class AnyTest extends TestCase
{
	public function testAnything()
	{
		dump(
			b('\t2 => fn(AlgumController $c): \\Closure => $c->algumMetodo2(...),\n')
				->match('/(\d) => .*->([\dA-z]+)\(\.\.\.\)/')
		);
		
		self::assertSame(0, 0);
	}
}