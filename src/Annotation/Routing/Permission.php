<?php

namespace App\Annotation\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Permission
{
	public function __construct(
		public readonly string $name
	) {}
}