<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use function Symfony\Component\String\b;

abstract class Model implements JsonSerializable
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected int $id = 0;

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn(ReflectionMethod $m) => $this->isGetter($m->name))
            ->mapWithKeys(
                fn(ReflectionMethod $m) => $m->getNumberOfRequiredParameters()
                    ? [0 => null]
                    : [b($m->name)->trimPrefix('get')->camel()->toString() => $m->invoke($this)]
            )
            ->filter()
            ->all();
    }

    private function isGetter(string $methodName): bool
    {
        return
            //É prefixado por 'get'
            ($name = b($methodName)->trimPrefix('get'))->equalsTo($name) &&
            //Getter tem tipo de retorno
            ($mType = (new ReflectionMethod($this, $methodName))->getReturnType()) &&
            //Tem propriedade respectiva ao getter
            property_exists($this, $prop = $name->camel()->toString()) &&
            //Propriedade tem tipo de retorno
            ($pType = (new ReflectionProperty($this, $prop))->getType()) /*&&
            //Os tipos batem
            $mType->getName() === $pType->getName()*/;
    }
}