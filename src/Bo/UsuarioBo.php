<?php

namespace App\Bo;

use App\Entity\Model;
use App\Entity\Usuario;

class UsuarioBo extends EntityBo
{
	public function store(Model $model): void
	{
		parent::store(self::criptografarSenha($model));
	}
	
	private static function criptografarSenha(Model $usuario): Usuario
	{
		/** @var Usuario $usuario */
		return $usuario->setSenha(password_hash($usuario->getSenha(), PASSWORD_DEFAULT));
	}
	
	public function getUserByCredentials(Usuario $usuario): ?Usuario
	{
		return self::getRepository()->all()
			->first(
				fn(Usuario $u) => $u->getLogin() == $usuario->getLogin()
					&& password_verify($usuario->getSenha(), $u->getSenha())
			);
	}
}