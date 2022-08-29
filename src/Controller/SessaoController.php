<?php

namespace App\Controller;

use App\Annotation\Routing\RouteParams;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessaoController extends Controller implements ISearcherController
{
    public function open(Request $request): Response
    {
        /** @var Sessao $sessao */
        $sessao = $this->deserialize($request->getContent(), Sessao::class);
        self::getFacade()->store($sessao);
        //return $this->json((new UsuarioFacade(self::getManager()))->byId($sessao->getUsuario()->getId()));
        return $this->json($sessao);
        return new Response('Sessão iniciada com sucesso!', Response::HTTP_CREATED);
    }

    #[RouteParams(['id'])]
    public function byId(int $id): JsonResponse
    {
        return $this->json(self::getFacade()->byId($id));
    }

    public function all(): JsonResponse
    {
        return $this->json(self::getFacade()->all());
    }

    public function close(Request $request): Response
    {
        /** @var Sessao $sessao */
        $sessao = $this->deserialize($request->getContent(), Sessao::class);
        if (!$sessao->getId())
            return new Response('A sessão a ser finalizada não existe', Response::HTTP_INTERNAL_SERVER_ERROR);
        self::getFacade()->store($sessao->setAtivo(false));
        return new Response('Sessão finalizada com sucesso!', Response::HTTP_OK);
    }
}