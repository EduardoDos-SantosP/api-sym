<?php

namespace App\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface IStorerController
{
    public function store(Request $request): Response;

    public function delete(Request $request): Response;
}