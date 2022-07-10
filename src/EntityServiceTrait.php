<?php

namespace App;

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

    public static function createFromEntity(string $entityName, mixed ...$arguments): IEntityService
    {
        $explodedNamespace = explode('\\', $class = static::class);
        if (count($explodedNamespace) <= 2)
            throw new RuntimeException("O serviço $class deve estar em um pasta adequada!");

        return self::findByEntity($entityName, $explodedNamespace[1], ...$arguments);
    }

    public static function findByEntity(
        string  $entityName,
        string  $serviceType,
        mixed   ...$arguments
    ): IEntityService
    {
        if (!class_exists($entityName))
            throw new RuntimeException("A entidade $entityName não existe!");

        $serviceType = b($serviceType)->lower()->title(true)->toString();

        $explodedNamespace = explode('\\', $entityName);
        $explodedNamespace[1] = $serviceType;
        if (!class_exists($class = implode('\\', $explodedNamespace) . $serviceType))
            throw new RuntimeException(
                "Não foi possível encontrar o serviço $class com base no serviço $entityName!"
            );

        if (!(new ReflectionClass($class))->implementsInterface($i = IEntityService::class))
            throw new RuntimeException("O serviço $class deve implementar a interface $i!");

        return new $class(...$arguments);
    }
}