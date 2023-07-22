<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
	public function index(Request $request): Response
	{
		//return $this->json(['Parâmetros' => $request->query->all()]);
		return new Response();//$this->redirectToRoute('app_loadroutes');
	}
	
	public function about(): Response
	{
		return new Response();
	}
}