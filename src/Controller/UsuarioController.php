<?php

namespace App\Controller;

use App\Annotation\Routing\RouteParams;
use App\Contract\ISearcherController;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends Controller implements ISearcherController
{
    public function all(): JsonResponse
    {
        return $this->json(self::getFacade()->all());
    }

    #[RouteParams(['id'])]
    public function byId(int $id): JsonResponse
    {
        return $this->json(self::getFacade()->byId($id));
    }

    #[RouteParams(['nome', 'login', 'senha'])]
    public function new(Request $request): JsonResponse
    {
        $u = $this->deserialize($request->getContent(), Usuario::class);
        //self::getFacade()->store($u);
        return $this->json($u);
    }
}