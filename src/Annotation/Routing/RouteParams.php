<?php

namespace App\Annotation\Routing;

use Attribute;
use RuntimeException;

#[Attribute]
class RouteParams
{
    public function __construct(
        public readonly array $params,
        public readonly array $requirements = [],
        public readonly array $defaults = []
    )
    {
        if (count($this->requirements) + count($this->defaults))
            throw new RuntimeException(
                'Os atributos $requirements e $defaults não tem utilidade implementada!'
            );
    }

    public function toUri(): string
    {
        return '/' . collect($this->params)->map(fn($p) => '{' . $p . '}')->join('/');
    }
}