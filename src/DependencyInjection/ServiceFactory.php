<?php

namespace App\DependencyInjection;

use App\Entity\Model;
use App\EntityServiceInterface;
use InvalidArgumentException;

class ServiceFactory implements ServiceFactoryInterface
{
	/** @param class-string<Model> $modelName */
	public function __construct(
		private readonly string $modelName,
		private readonly array $arguments
	) {
		if (!is_a($modelName, Model::class, true))
			throw new InvalidArgumentException(
				$modelName . ' deve ser uma classe filha de ' . Model::class
			);
	}
	
	public function create(): EntityServiceInterface
	{
		return new ($this->modelName)(...$this->arguments);
	}
}