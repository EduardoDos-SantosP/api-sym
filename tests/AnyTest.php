<?php

namespace App\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnyTest extends TestCase
{
	public function testAnything()
	{
		$d = new DateTime();
		new ReflectionClass(AnyTest::class);
		dump($d->diff(new DateTime())->f * 100);
		
		self::assertSame(0, 0);
	}
}