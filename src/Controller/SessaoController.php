<?php

namespace App\Controller;

use App\Bo\SessaoBo;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessaoController extends EntityController implements ISearcherController
{
	public function open(Request $request): Response
	{
		/** @var Sessao $sessao */
		$sessao = $this->deserialize($request);
		
		if (!$sessao->getUsuario()?->getId())
			throw new RuntimeException('Nenhum usuário com id informado para o início da sessão!');
		
		/** @var SessaoBo $bo */
		$bo = $this->getBo();
		$bo->open($sessao);
		
		return new Response('Sessão iniciada com sucesso!', Response::HTTP_CREATED);
	}
	
	public function all(): JsonResponse
	{
		return $this->json($this->getBo()->all());
	}
	
	/*#[RouteOptions(parameters: ['id'])]
	public function byId(int $id): JsonResponse
	{
		return $this->json($this->getBo()->byId($id));
	}*/
	
	public function close(Request $request): Response
	{
		/** @var Sessao $sessao */
		$sessao = $this->deserialize($request);
		
		if (!$sessao->getId())
			throw new RuntimeException('A sessão precisa ter um id para ser fechada!');
		
		/** @var SessaoBo $bo */
		$bo = $this->getBo();
		$bo->close($sessao);
		
		return new Response('Sessão finalizada com sucesso!', Response::HTTP_OK);
	}
}