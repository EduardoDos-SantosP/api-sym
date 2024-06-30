<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Entity\UsuarioPermissoes;

class UsuarioPermissoesRepository extends Repository
{
	public function findByUsuario(Usuario $usuario): ?UsuarioPermissoes
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.usuario = :val')
			->setParameter('val', $usuario->getId())
			->getQuery()
			->getOneOrNullResult();
	}
}
