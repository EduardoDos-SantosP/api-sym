<?php

namespace App\Contract;

use App\Entity\Model;

interface IStorer
{
    public function store(Model $model): void;

    public function delete(Model $model): void;
}