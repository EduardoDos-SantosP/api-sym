<?php

namespace App\Helper;

use Closure;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

final class MetaHelper
{
	private function __construct() {}
	
	public static function getPublicMethods(string|ReflectionClass $class): ?Collection
	{
		try {
			$class = is_string($class) ? new ReflectionClass($class) : $class;
		} catch (ReflectionException $e) {
			return null;
		}
		return collect($class->getMethods(ReflectionMethod::IS_PUBLIC));
	}
	
	public static function getFunctionName(Closure $callable): string
	{
		return (new ReflectionFunction($callable))->name;
	}
	
	public static function getAttribute(
		ReflectionFunctionAbstract|ReflectionClass|ReflectionProperty $reflection,
		string $attributeClassName,
		bool $throwsIfMoreThanOneFound = true
	): ?ReflectionAttribute {
		return match (count($attributes = $reflection->getAttributes($attributeClassName))) {
			0 => null,
			1 => $attributes[0],
			default => !$throwsIfMoreThanOneFound
				? $attributes[0]
				: throw new RuntimeException(
					sprintf(
						"A %s %s possui mais de um annotation com o nome '%s'!",
						$reflection::class,
						$reflection->getName(),
						$attributeClassName
					)
				)
		};
	}
}