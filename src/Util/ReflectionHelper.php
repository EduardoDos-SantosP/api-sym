<?php

namespace App\Util;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

final class ReflectionHelper
{
	private readonly object $obj;
	private readonly string $class;
	
	public function __construct(object|string $objOrClass)
	{
		$obj = $objOrClass;
		if (is_string($objOrClass)) {
			if (!class_exists($objOrClass))
				throw new RuntimeException("A classe $objOrClass não existe!");
			$obj = (new ReflectionClass($objOrClass))->newInstanceWithoutConstructor();
		}
		
		$this->obj = $obj;
		$this->class = get_class($obj);
	}
	
	/** Retorna um ReflectionAttribute de $class com nome de classe $attribute, ou null se não for encontrado */
	public function getAttrFromClass(string $attribute): ?ReflectionAttribute
	{
		return self::attributeFromClass($this->class, $attribute);
	}
	
	/** Retorna um ReflectionAttribute de $class com nome de classe $attribute, ou null se não for encontrado */
	public static function attributeFromClass(
		string|ReflectionClass $class,
		string $attribute
	): ?ReflectionAttribute {
		if (is_string($class)) {
			if (!class_exists($class))
				throw new RuntimeException("A classe $class não existe!");
			$class = new ReflectionClass($class);
		}
		
		return !($attributes = $class->getAttributes($attribute)) ? null : $attributes[0];
	}
	
	/** Retorna um ReflectionAttribute de $methodOrProp com nome de classe $attribute, ou null se não for encontrado */
	public function getAttrFromMethodOrProp(string $methodOrProp, string $attribute): ?ReflectionAttribute
	{
		return self::attributeFromMethodOrProp($this->obj, $methodOrProp, $attribute);
	}
	
	/** Retorna um ReflectionAttribute de $methodOrProp com nome de classe $attribute, ou null se não for encontrado */
	public static function attributeFromMethodOrProp(
		object $obj,
		string $methodOrProp,
		string $attribute
	): ?ReflectionAttribute {
		if (method_exists($obj, $methodOrProp))
			$reflection = new ReflectionMethod($obj, $methodOrProp);
		else if (property_exists($obj, $methodOrProp))
			$reflection = new ReflectionProperty($obj, $methodOrProp);
		else
			throw new RuntimeException("O método ou prorpiedade $methodOrProp não existe na classe!");
		
		return !($attributes = $reflection->getAttributes($attribute)) ? null : $attributes[0];
	}
	
	/** Retorna todos os métodos públicos da classe $class
	 * @return ReflectionMethod[]
	 */
	public function getPublicMethods(): array
	{
		return self::publicMethodsOf($this->class);
	}
	
	/** Retorna todos os métodos públicos da classe $class
	 * @return ReflectionMethod[]
	 */
	public static function publicMethodsOf(string $class): array
	{
		if (!class_exists($class))
			throw new RuntimeException("A classe $class não existe!");
		return (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC);
	}
}