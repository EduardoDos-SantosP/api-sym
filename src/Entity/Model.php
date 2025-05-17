<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\PersistentCollection;
use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;
use Throwable;
use function Symfony\Component\String\b;

abstract class Model implements JsonSerializable
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected ?int $id = 0;

    public function __construct(int $id = 0, array $map = null)
    {
        $this->id = max([$id, 0]);
        if (!$map) return;

        foreach ($map as $prop => $value) {
            if (!method_exists($this, $setter = 'set' . ucfirst($prop)))
                throw new RuntimeException(
                    "O setter de '$prop' não foi encontrado na classe " . static::class . '!'
                );

            if (!(new ReflectionMethod($this, $setter))->getNumberOfRequiredParameters())
                throw new RuntimeException("O setter $setter é inválido pois não recebe argumentos!");

            try {
                $this->{$setter}($value);
            } catch (Throwable $e) {
                throw new RuntimeException(
                    message: "Não foi possível atribuir valor a propriedade $prop!",
                    previous: $e
                );
            }
        }
    }

    public static function getProperties(): array
    {
        $reflect = new ReflectionClass(static::class);
        $getMethod = fn(string $name) => $reflect->hasMethod($name) ? $reflect->getMethod($name)->name : null;

        return collect($reflect->getProperties())
            ->lazy()
            ->where(fn(ReflectionProperty $p) => $p->getAttributes(Column::class))
            ->mapWithKeys(fn(ReflectionProperty $p) => [
                $p->name => (object)[
                    'property' => $p->name,
                    'getter' => $getMethod('get' . ucfirst($p->name)),
                    'setter' => $getMethod('set' . ucfirst($p->name)),
                    'type' => (string)$p->getType()
                ]
            ])->all();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function jsonSerialize(): mixed
    {
        return collect((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn(ReflectionMethod $m) => $this->isGetter($m->name))
            ->mapWithKeys(
                fn(ReflectionMethod $m) => $m->getNumberOfRequiredParameters()
                    ? [0 => null]
                    : [
                        b($m->name)->trimPrefix('get')->camel()->toString() =>
                            (
                            is_a($v = $m->invoke($this), PersistentCollection::class)
                                ? $v->map(fn($i) => ['id' => $i->getId()])->getValues()
                                : $v
                            )
                    ]
            )
            ->filter()
            ->all();
    }

    private function isGetter(string $methodName): bool
    {
        return
            //É prefixado por 'get'
            !($name = b($methodName)->trimPrefix('get'))->equalsTo($methodName) &&
            //Getter tem tipo de retorno
            ($mType = (new ReflectionMethod($this, $methodName))->getReturnType()) &&
            //Tem propriedade respectiva ao getter
            property_exists($this, $prop = $name->camel()->toString()) &&
            //Propriedade tem tipo de retorno
            ($pType = (new ReflectionProperty($this, $prop))->getType()) /*&&
            //Os tipos batem
            $mType->getName() === $pType->getName()*/ ;
    }
}