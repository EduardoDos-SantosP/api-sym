<?php

namespace App\Controller;

use App\Annotation\Routing\RouteOptions;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use App\Facade\SessaoFacade;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessaoController extends Controller implements ISearcherController
{
	public function open(Request $request): Response
	{
		/** @var Sessao $sessao */
		$sessao = $this->deserialize($request);
		
		if (!$sessao->getUsuario()?->getId())
			throw new RuntimeException('Nenhum usuário com id informado para o início da sessão!');
		
		/** @var SessaoFacade $facade */
		$facade = self::getFacade();
		$facade->open($sessao);
		
		return new Response('Sessão iniciada com sucesso!', Response::HTTP_CREATED);
	}
	
	public function all(): JsonResponse
	{
		return $this->json(self::getFacade()->all());
	}
	
	#[RouteOptions(parameters: ['id'])]
	public function byId(int $id): JsonResponse
	{
		return $this->json(self::getFacade()->byId($id));
	}
	
	public function close(Request $request): Response
	{
		/** @var Sessao $sessao */
		$sessao = $this->deserialize($request);
		
		if (!$sessao->getId())
			throw new RuntimeException('A sessão precisa ter um id para ser fechada!');
		
		/** @var SessaoFacade $facade */
		$facade = self::getFacade();
		$facade->close($sessao);
		
		return new Response('Sessão finalizada com sucesso!', Response::HTTP_OK);
	}
}