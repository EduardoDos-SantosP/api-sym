<?php

namespace App\Contract;

use App\Entity\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

interface ISearcherController
{
	public function all(): JsonResponse;
	
	public function byId(Model $model): JsonResponse;
}