<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use ReflectionMethod;
use ReflectionProperty;
use function Symfony\Component\String\b;

abstract class Model
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected int $id = 0;

    private function isGetter(string $methodName): bool
    {
        return
            //É prefixado por 'get'
            ($name = b($methodName))->trimPrefix('get')->equalsTo($name) &&
            //Getter tem tipo de retorno
            ($mType = (new ReflectionMethod($this, $methodName))->getReturnType()) &&
            //Tem propriedade respectiva ao getter
            property_exists($this, $prop = $name->camel()->toString()) &&
            //Propriedade tem tipo de retorno
            ($pType = (new ReflectionProperty($this, $prop))->getType()) &&
            //Os tipos batem
            $mType->getName() === $pType->getName();
    }
}