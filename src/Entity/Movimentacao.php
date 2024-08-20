<?php

namespace App\Entity;

use App\Repository\MovimentacaoRepository;
use App\Util\DateTimeLocal;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity(MovimentacaoRepository::class)]
class Movimentacao extends Model
{
    #[Column(type: 'string', length: 127)]
    private string $nome;

    #[Column(type: 'string', length: 511, nullable: true)]
    private string $descricao;

    #[Column(type: 'float')]
    private float $valor;

    #[Column(type: 'datetime')]
    private DateTimeInterface $data;

    #[OneToMany(mappedBy: 'movimentacao', targetEntity: MovimentacaoItem::class)]
    private Collection $items;

    public function __construct(int $id = 0, array $map = null)
    {
        parent::__construct($id, $map);
        $this->data = new DateTimeLocal();
        $this->items = new ArrayCollection();
    }

    public function getNome(): ?string
    {
        return $this->nome ?? null;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): void
    {
        $this->items = $items;
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
