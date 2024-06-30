<?php

namespace App\Annotation\Routing;

use Attribute;
use RuntimeException;

#[Attribute]
class RouteOptions
{
	public function __construct(
		public readonly ?string $path = null,
		public readonly array $parameters = [],
		public readonly array $requirements = [],
		public readonly array $defaults = [],
	
	) {
		if (count($this->requirements) + count($this->defaults))
			throw new RuntimeException(
				'Os atributos $requirements e $defaults não tem utilidade implementada!'
			);
	}
	
	public function toUri(): string
	{
		return collect($this->parameters)->map(fn($p) => '/{' . $p . '}')->join('');
	}
}