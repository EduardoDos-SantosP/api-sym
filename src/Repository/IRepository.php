<?php

namespace App\Repository;

use App\Contract\ISearcher;
use App\Contract\IStorer;
use App\Entity\Model;
use Illuminate\Support\Collection;

interface IRepository extends ISearcher, IStorer
{
    public function all(): Collection;

    public function byId(int $id): ?Model;

    public function store(Model $model): void;

    public function delete(Model $model): void;
}