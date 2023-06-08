<?php

namespace App\Bo;

use App\Entity\Usuario;
use App\Entity\UsuarioPermissoes;
use App\Repository\UsuarioPermissoesRepository;

class UsuarioPermissoesBo extends EntityBo
{
	public function findByUsuario(Usuario $usuario): ?UsuarioPermissoes
	{
		/** @var UsuarioPermissoesRepository $repo */
		$repo = $this->getRepository();
		return $repo->findByUsuario($usuario);
	}
}