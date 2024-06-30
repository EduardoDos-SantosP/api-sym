<?php

namespace App\Util;

use Closure;

final class Singleton
{
	/** @var $instances array<string, object> */
	private static array $instances = [];
	
	private function __construct() {}
	
	public static function getInstance(string $key, Closure $factory, mixed ...$arguments): object
	{
		if (isset(self::$instances[$key])) return self::$instances[$key];
		self::$instances[$key] = $instance = $factory(...$arguments);
		return $instance;
	}
}