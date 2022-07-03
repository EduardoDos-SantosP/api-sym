<?php

namespace App\Annotation\Routing;

use Attribute;

#[Attribute]
class RouteParams
{
    public function __construct(
        public readonly array $params,
        public readonly array $requirements = [],
        public readonly array $defaults = []
    )
    {
    }

    public function toUri(): string
    {
        return '/' . collect($this->params)->map(fn($p) => '{' . $p . '}')->join('/');
    }
}