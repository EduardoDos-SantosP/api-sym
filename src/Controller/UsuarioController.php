<?php

namespace App\Controller;

use App\Annotation\Routing\NotAuthenticate;
use App\Annotation\Routing\RouteOptions;
use App\Bo\UsuarioBo;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use App\Entity\Usuario;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends EntityController implements ISearcherController
{
	#[NotAuthenticate]
	public function new(Request $request): JsonResponse
	{
		/** @var Usuario $u */
		$u = $this->deserialize($request);
		self::getBo()->store($u);
		return $this->json($u);
	}
	
	#[NotAuthenticate]
	public function login(Request $request, RouteAuthenticator $authenticator): JsonResponse
	{
		/** @var UsuarioBo $service */
		$service = self::getBo();
		$usuario = $service->getUserByCredentials($this->deserialize($request));
		
		if (!isset($usuario))
			throw new RuntimeException('As credenciais estão incorretas!');
		
		/** @var JsonResponse $response */
		$response = $authenticator->generateTokenedResponse(new Sessao($usuario));
		
		return $response;
	}
	
	public function all(): JsonResponse
	{
		return $this->json(self::getBo()->all());
	}
	
	#[RouteOptions(parameters: ['id'])]
	public function byId(int $id): JsonResponse
	{
		return $this->json(self::getBo()->byId($id));
	}
	
	public function delete(Usuario $usuario): JsonResponse
	{
		return $this->json($usuario);
	}
}