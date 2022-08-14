<?php

namespace App\Contract;

use App\Entity\Model;
use Illuminate\Support\Collection;

interface ISearcher
{
    public function all(): Collection;

    public function byId(int $id): ?Model;
}