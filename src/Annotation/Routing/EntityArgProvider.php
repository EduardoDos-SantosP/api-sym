<?php

namespace App\Annotation\Routing;

use App\Entity\Model;
use App\Enum\EnumArgProviderMode;
use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class EntityArgProvider
{
	public function __construct(
		private readonly EnumArgProviderMode $mode = EnumArgProviderMode::Deserialize,
		private readonly ?string $classToDeserialize = null
	) {
		if ($this->classToDeserialize &&
			!is_a($this->classToDeserialize, Model::class, true))
			throw new InvalidArgumentException(
				'Não foi possível indentificar o valor de $classToDeserialize (\'' .
				$this->classToDeserialize . '\') como uma classe que estende de ' . Model::class
			);
	}
	
	public function getMode(): EnumArgProviderMode
	{
		return $this->mode;
	}
	
	public function getClassToDeserialize(): ?string
	{
		return $this->classToDeserialize;
	}
}