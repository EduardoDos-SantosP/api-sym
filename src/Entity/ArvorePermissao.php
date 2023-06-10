<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;

abstract class ArvorePermissao extends Model
{
	#[ManyToMany(targetEntity: Permissao::class/*, fetch: 'EAGER'*/)]
	#[JoinTable(name: 'usuario_permissoes_permissao')]
	protected Collection $permissoes;
	
	public function __construct(int $id = 0, array $map = null)
	{
		parent::__construct($id, $map);
		$this->permissoes = new ArrayCollection();
	}
	
	public function buscarPermissao(string $nomePermissao): ?Permissao
	{
		foreach ($this->getPermissoes() as $permissaoFilha) {
			if ($permissaoFilha->getNome() === $nomePermissao)
				return $permissaoFilha;
			$permissao = $permissaoFilha->buscarPermissao($nomePermissao);
			if ($permissao) return $permissao;
		}
		return null;
	}
	
	/** @return Collection<int, Permissao> */
	public function getPermissoes(): Collection
	{
		return $this->permissoes ??= new ArrayCollection();
	}
	
	public function addPermisso(Permissao $permisso): static
	{
		if (!$this->permissoes->contains($permisso)) {
			$this->permissoes->add($permisso);
		}
		
		return $this;
	}
	
	public function removePermisso(Permissao $permisso): static
	{
		$this->permissoes->removeElement($permisso);
		
		return $this;
	}
}