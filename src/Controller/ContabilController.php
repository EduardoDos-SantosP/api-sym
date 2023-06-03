<?php

namespace App\Controller;

use App\Entity\Contabil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContabilController extends EntityController
{
	public function index(): JsonResponse
	{
		$conta = new Contabil();
		$conta->setNome('teste');
		return $this->json($conta);
	}
	
	public function all(): JsonResponse
	{
		return $this->json($this->getBo()->all());
	}
	
	public function new(Contabil $contabil): Response
	{
		$this->getBo()->store($contabil);
		
		return $this->json($contabil);
	}
}
