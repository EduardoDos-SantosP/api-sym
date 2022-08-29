<?php

namespace App\Controller;

use App\Annotation\Routing\RouteParams;
use App\Contract\ISearcherController;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

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

    public function new(Request $request, SerializerInterface $serializer): JsonResponse
    {
        /** @var Usuario $u */
        $u = $serializer->deserialize($request->getContent(), Usuario::class, 'json');
        self::getFacade()->store($u);
        return $this->json($u);
    }
}