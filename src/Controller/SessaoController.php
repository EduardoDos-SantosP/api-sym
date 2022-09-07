<?php

namespace App\Controller;

use App\Annotation\Routing\RouteParams;
use App\Contract\ISearcherController;
use App\Entity\Sessao;
use App\Facade\SessaoFacade;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class SessaoController extends Controller implements ISearcherController
{
    public function open(Request $request, SerializerInterface $serializer): Response
    {
        /** @var Sessao $sessao */
        $sessao = $this->deserialize($request->getContent(), $serializer, Sessao::class);

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

    public function close(Request $request, SerializerInterface $serializer): Response
    {
        /** @var Sessao $sessao */
        $sessao = $this->deserialize($request->getContent(), $serializer, Sessao::class);

        if (!$sessao->getId())
            throw new RuntimeException('A sessão precisa ter um id para ser fechada!');

        /** @var SessaoFacade $facade */
        $facade = self::getFacade();
        $facade->close($sessao);

        return new Response('Sessão finalizada com sucesso!', Response::HTTP_OK);
    }

    #[RouteParams(['id'])]
    public function byId(int $id): JsonResponse
    {
        return $this->json(self::getFacade()->byId($id));
    }
}