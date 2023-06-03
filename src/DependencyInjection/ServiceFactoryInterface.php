<?php

namespace App\DependencyInjection;

use App\EntityServiceInterface;

interface ServiceFactoryInterface
{
	function create(): EntityServiceInterface;
}