<?php

namespace App\Controller;

use App\Annotation\Routing\NotAuthenticate;
use App\Bo\UsuarioBo;
use App\Contract\ISearcherController;
use App\Entity\Model;
use App\Entity\Sessao;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends EntityController implements ISearcherController
{
	#[NotAuthenticate]
	public function store(Model $usuario): JsonResponse
	{
		$this->getBo()->store($usuario);
		return $this->json($usuario);
	}
	
	#[NotAuthenticate]
	public function login(Request $request, RouteAuthenticator $authenticator): JsonResponse
	{
		/** @var UsuarioBo $service */
		$service = $this->getBo();
		$usuario = $service->getUserByCredentials($this->deserialize($request));
		
		if (!isset($usuario))
			throw new RuntimeException('As credenciais estão incorretas!');
		
		/** @var JsonResponse $response */
		$response = $authenticator->generateTokenedResponse(new Sessao($usuario));
		
		return $response;
	}
}