<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', 'home_')]
class HomeController extends Controller
{
    #[Route('/index', 'index')]
    public function index(): Response
    {
        return self::createResponse('Olá mundo!');
    }
}