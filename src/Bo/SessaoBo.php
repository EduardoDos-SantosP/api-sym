<?php

namespace App\Bo;

use App\Entity\Sessao;
use App\Entity\Usuario;
use App\Helper\DateTimeLocal;

class SessaoBo extends EntityBo
{
	public function open(Sessao $sessao): void
	{
		//TODO: validar a a existência de mais de uma sessão aberta por usuário
		
		$usuarioBo = new UsuarioBo(self::getManager());
		
		/** @var Usuario $usuario */
		$usuario = $usuarioBo->byId($sessao->getUsuario()->getId());
		
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