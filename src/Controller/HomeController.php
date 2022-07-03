<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->json('Deu certo!');
    }

    public function about(): Response
    {
        return new Response();
    }
}