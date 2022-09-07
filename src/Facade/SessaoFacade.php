<?php

namespace App\Facade;

use App\Entity\Model;
use App\Entity\Sessao;
use App\Entity\Usuario;
use App\Helper\DateTimeLocal;
use RuntimeException;

class SessaoFacade extends EntityFacade
{


    public function open(Sessao $sessao): Sessao
    {
        /** @var Usuario $usuario */
        $usuario = (new UsuarioFacade(self::getManager()))
            ->byId($sessao->getUsuario()->getId());

        /*$sessao->setUsuario($usuario);
        return $sessao;*/

        if (!$usuario)
            throw new RuntimeException('Nenhum usuário informado para o início da sessão!');

        self::getRepository()->store(
            $sessao
                ->setUsuario($usuario)
                ->setAtivo(true)
                ->setDataInicio(new DateTimeLocal())
        );
        return $sessao;
    }

    /** @param $model Sessao */
    public function store(Model $model): void
    {
        $this->{$model->getId() ? 'close' : 'open'}($model);
    }

    public function close(Sessao $sessao): Sessao
    {

    }
}