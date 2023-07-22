<?php

namespace App\Enum;

enum EnumArgProviderMode implements IEnum
{
	use BaseEnumTrait;
	
	case Deserialize;
	case Query;
	case Merge;
}