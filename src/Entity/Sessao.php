<?php

namespace App\Entity;

use App\Repository\SessaoRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: SessaoRepository::class)]
class Sessao extends Model
{
    #[ManyToOne(targetEntity: Usuario::class)]
    #[JoinColumn(nullable: false)]
    private Usuario $usuario;

    #[Column(type: 'datetime')]
    private DateTimeInterface $dataInicio;

    #[Column(type: 'datetime', nullable: true)]
    private DateTimeInterface $dataFim;

    #[Column(type: 'boolean')]
    private bool $ativo;

    public function getUsuario(): ?Usuario
    {
        return $this->usuario ?? null;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getDataInicio(): ?DateTimeInterface
    {
        return $this->dataInicio ?? null;
    }

    public function setDataInicio(DateTimeInterface $dataInicio): self
    {
        $this->dataInicio = $dataInicio;

        return $this;
    }

    public function getDataFim(): ?DateTimeInterface
    {
        return $this->dataFim ?? null;
    }

    public function setDataFim(?DateTimeInterface $dataFim): self
    {
        $this->dataFim = $dataFim;

        return $this;
    }

    public function isAtivo(): ?bool
    {
        return $this->ativo ?? null;
    }

    public function setAtivo(bool $ativo): self
    {
        $this->ativo = $ativo;

        return $this;
    }
}
