<?php

namespace App\Repository;

use App\Entity\Model;

interface IRepository
{
    public function all(): array;

    public function byId(int $id): Model;

    public function store(Model $entity): void;

    public function delete(Model $entity): void;
}