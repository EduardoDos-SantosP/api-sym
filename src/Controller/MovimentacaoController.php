<?php

namespace App\Controller;

use App\Entity\Movimentacao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MovimentacaoController extends EntityController
{
	public function index(): JsonResponse
	{
		$conta = new Movimentacao();
		$conta->setNome('teste');
		return $this->json($conta);
	}
	
	public function new(Movimentacao $contabil): Response
	{
		$this->getBo()->store($contabil);
		
		return $this->json($contabil);
	}
}
