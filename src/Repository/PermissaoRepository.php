<?php

namespace App\Repository;

use App\Entity\Permissao;

class PermissaoRepository extends Repository
{
	public function findByName(string $name): ?Permissao
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.nome = :val')
			->setParameter('val', $name)
			->getQuery()
			->getOneOrNullResult();
	}
}
