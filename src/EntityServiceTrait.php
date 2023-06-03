<?php

namespace App;

use App\Enum\EnumServiceType;
use OutOfBoundsException;
use ReflectionClass;
use RuntimeException;
use function Symfony\Component\String\b;

trait EntityServiceTrait
{
	public static function getModelName(): string
	{
		$explodedNamespace = explode('\\', $service = static::class);
		if (count($explodedNamespace) <= 2)
			throw new OutOfBoundsException(
				"Não foi possível indentificar o tipo de serviço da classe $service,
                pois essa se encontra na raiz!"
			);
		
		$serviceType = $explodedNamespace[1];
		$entityFolder = 'Entity';
		$explodedNamespace[1] = $entityFolder;
		$class = b(implode('\\', $explodedNamespace))->trimSuffix($serviceType)->toString();
		if (!class_exists($service))
			throw new RuntimeException(
				"Não foi possível encontrar a entidade $class com base no serviço $service!"
			);
		
		return $class;
	}
	
	public static function createFromEntity(string $entityName, mixed ...$arguments): EntityServiceInterface
	{
		$explodedNamespace = explode('\\', $class = static::class);
		if (count($explodedNamespace) <= 2)
			throw new RuntimeException("O serviço $class deve estar em um pasta adequada!");
		
		if (!($serviceType = $explodedNamespace[1]))
			throw new RuntimeException(
				sprintf("O caso '%s' não existe no enumerador %s!", $serviceType, EnumServiceType::class)
			);
		
		return self::findByEntity($entityName, EnumServiceType::getByName($serviceType), ...$arguments);
	}
	
	public static function findByEntity(
		string $entityName,
		EnumServiceType $serviceType,
		mixed           ...$arguments
	): EntityServiceInterface {
		if (!class_exists($entityName))
			throw new RuntimeException("A entidade $entityName não existe!");
		
		$serviceType = b($serviceType->name)->lower()->title(true)->toString();
		
		$explodedNamespace = explode('\\', $entityName);
		$explodedNamespace[1] = $serviceType;
		if (!class_exists($class = implode('\\', $explodedNamespace) . $serviceType))
			throw new RuntimeException(
				"Não foi possível encontrar o serviço '$class' com base no serviço $entityName!"
			);
		
		if (!(new ReflectionClass($class))->implementsInterface($i = EntityServiceInterface::class))
			throw new RuntimeException("O serviço $class deve implementar a interface $i!");
		
		return new $class(...$arguments);
	}
}