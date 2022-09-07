<?php

namespace App\Facade;

use App\Entity\Sessao;
use App\Entity\Usuario;
use App\Helper\DateTimeLocal;

class SessaoFacade extends EntityFacade
{
    public function open(Sessao $sessao): void
    {
        //TODO: validar a a existência de mais de uma sessão aberta por usuário

        $usuarioFacade = new UsuarioFacade(self::getManager());

        /** @var Usuario $usuario */
        $usuario = $usuarioFacade->byId($sessao->getUsuario()->getId());

        self::getRepository()->store(
            (new Sessao())
                ->setAtivo(true)
                ->setUsuario($usuario)
                ->setDataInicio(new DateTimeLocal())
        );
    }

    public function close(Sessao $sessao): void
    {
        $repository = self::getRepository();

        /** @var Sessao $sessao */
        $sessao = $repository->byId($sessao->getId());

        $repository->store(
            $sessao
                ->setAtivo(false)
                ->setDataFim(new DateTimeLocal())
        );
    }
}