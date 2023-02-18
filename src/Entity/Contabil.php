<?php

namespace App\Entity;

use App\Repository\ContabilRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity(ContabilRepository::class)]
class Contabil extends Model
{
	#[Column(type: 'string', length: 127)]
	private string $nome;
	
	#[Column(type: 'string', length: 511, nullable: true)]
	private string $descricao;
	
	#[Column(type: 'float')]
	private float $valor;
	
	#[Column(type: 'datetime')]
	private DateTimeInterface $data;
	
	public function getNome(): ?string
	{
		return $this->nome ?? null;
	}
	
	public function setNome(string $nome): self
	{
		$this->nome = $nome;
		
		return $this;
	}
	
	public function getDescricao(): ?string
	{
		return $this->descricao ?? null;
	}
	
	public function setDescricao(?string $descricao): self
	{
		$this->descricao = $descricao;
		
		return $this;
	}
	
	public function getValor(): ?float
	{
		return $this->valor ?? null;
	}
	
	public function setValor(float $valor): self
	{
		$this->valor = $valor;
		
		return $this;
	}
	
	public function getData(): ?DateTimeInterface
	{
		return $this->data ?? null;
	}
	
	public function setData(DateTimeInterface $data): self
	{
		$this->data = $data;
		
		return $this;
	}
}
