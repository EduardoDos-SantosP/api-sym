<?php

namespace App\Controller;

use App\Annotation\Routing\RouteOptions;
use App\Bo\MovimentacaoItemBo;
use App\Entity\Movimentacao;
use App\Entity\MovimentacaoItem;
use App\Enum\EnumServiceType;
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

    #[RouteOptions(path: '/movimentacao/items', parameters: ['id'])]
    public function upsertItem(MovimentacaoItem $item, int $id): Response
    {
        /** @var Movimentacao $movimentacao */
        $movimentacao = $this->getBo()->byId($id);
        if (!$movimentacao)
            return $this->json(
                ['message' => "Movimentação não encontrada para o id '$id'"],
                Response::HTTP_NOT_FOUND
            );
        $item->setMovimentacao($movimentacao);

        /** @var MovimentacaoItemBo $bo */
        $bo = $this->serviceLocator->getServiceInstance(
            EnumServiceType::Bo,
            MovimentacaoItem::class
        );
        $bo->store($item);
        return $this->json($item);
    }
}
