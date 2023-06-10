<?php

namespace App\Entity;

use App\Repository\UsuarioPermissoesRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: UsuarioPermissoesRepository::class)]
class UsuarioPermissoes extends ArvorePermissao
{
	#[OneToOne(cascade: ['persist', 'remove'])]
	#[JoinColumn(nullable: false)]
	private ?Usuario $usuario = null;
	
	public function getUsuario(): ?Usuario
	{
		return $this->usuario;
	}
	
	public function setUsuario(Usuario $usuario): self
	{
		$this->usuario = $usuario;
		
		return $this;
	}
}
