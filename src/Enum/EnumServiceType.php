<?php

namespace App\Enum;

enum EnumServiceType implements IEnum
{
    use BaseEnumTrait;

    case Controller;
    case Facade;
    case Repositoy;
}

dd(EnumServiceType::caseExists('Controller'));