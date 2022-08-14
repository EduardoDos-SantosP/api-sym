<?php

namespace App\Contract;

use Symfony\Component\HttpFoundation\JsonResponse;

interface ISearcherController
{
    public function all(): JsonResponse;

    public function byId(int $id): JsonResponse;
}