<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
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
        return $this->serializeRecursive();
    }

    private function serializeRecursive(array &$visited = []): array
    {
        $result = [];
        $refClass = new ReflectionClass($this);

        $objectId = spl_object_id($this);
        if (isset($visited[$objectId])) {
            // Retorna somente o ID para evitar referência circular
            return ['id' => $this->getId()];
        }

        $visited[$objectId] = true;

        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!$this->isGetter($method->name)) continue;
            if ($method->getNumberOfRequiredParameters() > 0) continue;

            $propName = b($method->name)->trimPrefix('get')->camel()->toString();
            $value = $method->invoke($this);

            if ($value instanceof Collection) {
                $result[$propName] = $value
                    ->map(fn($item) => $item instanceof self
                        ? $item->serializeRecursive($visited)
                        : $item
                    )
                    ->getValues();
            } elseif ($value instanceof self) {
                $result[$propName] = $value->serializeRecursive($visited);
            } else {
                $result[$propName] = $value;
            }
        }

        return $result;
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