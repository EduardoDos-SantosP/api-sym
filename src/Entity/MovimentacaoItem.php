<?php

namespace App\Entity;

use App\Entity\Model;
use App\Repository\MovimentacaoItemRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(MovimentacaoItemRepository::class)]
class MovimentacaoItem extends Model
{
    #[Column(type: 'string', length: 127)]
    private string $nome;

    #[Column(type: 'string', length: 511, nullable: true)]
    private string $descricao;

    #[Column(type: 'float')]
    private float $valor;

    #[ManyToOne(Movimentacao::class)]
    private Movimentacao $movimentacao;

    public function getMovimentacao(): Movimentacao
    {
        return $this->movimentacao;
    }

    public function setMovimentacao(Movimentacao $movimentacao): self
    {
        $this->movimentacao = $movimentacao;
        return $this;
    }

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
}