<?php

namespace App\Entity;

use App\Repository\PermissaoRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: PermissaoRepository::class)]
class Permissao extends ArvorePermissao
{
	#[Column(length: 127)]
	private ?string $nome = null;
	
	#[Column(length: 255, nullable: true)]
	private ?string $descricao = null;
	
	public function getNome(): ?string
	{
		return $this->nome;
	}
	
	public function setNome(string $nome): self
	{
		$this->nome = $nome;
		
		return $this;
	}
	
	public function getDescricao(): ?string
	{
		return $this->descricao;
	}
	
	public function setDescricao(?string $descricao): self
	{
		$this->descricao = $descricao;
		
		return $this;
	}
}
