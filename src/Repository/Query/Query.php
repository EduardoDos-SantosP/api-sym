<?php

namespace App\Repository\Query;

use App\Exception\NullArgumentException;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use ReflectionFunction;
use TypeError;

class Query
{
    private EntityManagerInterface $entityManager;
    private Closure $factory;
    private mixed $params;

    public function __construct(EntityManagerInterface $entityManager, Closure $factory, mixed $params = null)
    {
        if (!($this->entityManager = $entityManager))
            throw new NullArgumentException('$entityManager');

        if (!$factory) throw new NullArgumentException('$factory');
        if (!is_a($factory, Closure::class))
            throw new TypeError('O parâmetro $factory dever ser um callback!');

        $reflection = new ReflectionFunction($factory);
        $returnType = $reflection->getReturnType();

        if ($returnType &&
            /*is_a($returnType, ReflectionNamedType::class) &&
            $returnType->getName()*/ "$returnType" !== QueryBuilder::class)
            throw new InvalidArgumentException('$factory deve retornar um QueryBuilder!');

        $this->factory = $factory;
        $this->params = $params;
    }

    public function run(): mixed
    {
        $factory = $this->factory;
        /** @var QueryBuilder $qb */
        $qb = $factory($this->entityManager->createQueryBuilder(), $this->params);
        return $qb->getQuery()->execute();
    }
}