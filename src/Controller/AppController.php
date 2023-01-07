<?php

namespace App\Controller;

use App\Annotation\Routing\DevRoute;
use App\Annotation\Routing\RouteOptions;
use App\Other\RouteGenerator;
use Symfony\Component\HttpFoundation\Response;

class AppController extends Controller
{
	#[DevRoute]
	#[RouteOptions(path: '/')]
	public function loadRoutes(RouteGenerator $generator): Response
	{
		$generator->loadRoutes();
		return $this->json('Rotas geradas com sucesso!');
	}
}