<?php

namespace App\Controller;

use App\Annotation\Routing\RouteParams;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    public function index(ContainerBagInterface $b): Response
    {
        //return $this->redirect('/app/loadRoutes');
        //(new AppController())->autoLoadRoutes($b->get('kernel.project_dir'));
        return self::createResponse('Deu certo!');
    }

    public function about(): Response
    {
        return new Response();
    }
}