<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity(repositoryClass: UsuarioRepository::class)]
class Usuario extends Model
{
    #[Column(type: 'string', length: 127)]
    private string $nome;

    #[Column(type: 'string', length: 127)]
    private string $login;

    #[Column(type: 'string', length: 127)]
    private string $senha;

    public function getNome(): ?string
    {
        return $this->nome ?? null;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getLogin(): ?string
    {
        return $this->login ?? null;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getSenha(): ?string
    {
        return $this->senha ?? null;
    }

    public function setSenha(string $senha): void
    {
        $this->senha = $senha;
    }
}
