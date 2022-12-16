<?php

namespace App\Controller;

use App\Annotation\Routing\NotAuthenticate;
use App\Annotation\Routing\RouteParams;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use App\Entity\Usuario;
use App\Facade\UsuarioFacade;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends Controller implements ISearcherController
{
    #[NotAuthenticate]
    public function new(Request $request): JsonResponse
    {
        /** @var Usuario $u */
        $u = $this->deserialize($request);
        self::getFacade()->store($u);
        return $this->json($u);
    }

    #[NotAuthenticate]
    public function login(Request $request, RouteAuthenticator $authenticator): JsonResponse
    {
        /** @var UsuarioFacade $service */
        $service = self::getFacade();
        $usuario = $service->getUserByCredentials($this->deserialize($request));

        if (!isset($usuario))
            throw new RuntimeException('As credenciais estão incorretas!');

        /** @var JsonResponse $response */
        $response = $authenticator->generateTokenedResponse(new Sessao($usuario));

        return $response;
    }

    public function all(): JsonResponse
    {
        return $this->json(self::getFacade()->all());
    }

    #[RouteParams(['id'])]
    public function byId(int $id): JsonResponse
    {
        return $this->json(self::getFacade()->byId($id));
    }
}