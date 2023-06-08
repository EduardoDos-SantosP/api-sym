<?php

namespace App\Bo;

use App\Entity\Permissao;
use App\Repository\PermissaoRepository;

class PermissaoBo extends EntityBo
{
	public function findByName(string $name): ?Permissao
	{
		/** @var PermissaoRepository $repo */
		$repo = $this->getRepository();
		return $repo->findByName($name);
	}
}