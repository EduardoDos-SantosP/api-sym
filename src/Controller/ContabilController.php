<?php

namespace App\Controller;

use App\Entity\Contabil;
use App\Repository\ContabilRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContabilController extends Controller
{
    public function index(): JsonResponse
    {
        $conta = new Contabil();
        $conta->setNome('teste');
        return $this->json($conta);
    }

    public function newEnt(ContabilRepository $repository): JsonResponse
    {
        $c = new Contabil();
        $c->setNome('Segunda inserção');
        $c->setDescricao('Segunda inserção');
        $c->setData(new \DateTime());
        $c->setValor(2);
        $repository->add($c);

        return $this->json('Sucesso!');
    }
}
