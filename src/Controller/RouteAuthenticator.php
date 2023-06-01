<?php

namespace App\Controller;

use App\Entity\Sessao;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function Symfony\Component\String\b;

#[AsRoutingConditionService('authenticator')]
class RouteAuthenticator
{
	private const ALGORITHM = 'HS256';
	private const TOKEN_PREFIX = 'Bearer ';
	
	public function __construct(
		private readonly string $jwtSecret
	) {}
	
	public function authenticate(Request $request): bool
	{
		if ($_ENV['APP_ENV'] === 'dev' && !$request->query->getBoolean('authenticate'))
			return true;
		$dirtyToken = $request?->headers?->get('authorization');
		if (!$dirtyToken) return false;
		$token = preg_replace('/^' . self::TOKEN_PREFIX . '/i', '', trim($dirtyToken), 1);
		
		try {
			$decoded = (object)JWT::decode($token, new Key($this->jwtSecret, self::ALGORITHM));
		} catch (Throwable $e) {
			throw new BadRequestException(
				'Não foi possível desserializar o token de autenticação!',
				previous: $e
			);
		}
		
		return (int)$decoded->usr;
	}
	
	public function generateTokenedResponse(Sessao $sessao): Response
	{
		if (!$sessao->getUsuario()?->getId())
			throw new InvalidArgumentException('A sessão precisa ter um usuário com id!');
		
		$token = b(JWT::encode($sessao->tokenize(), $this->jwtSecret, self::ALGORITHM))
			->ensureStart(self::TOKEN_PREFIX)
			->toString();
		
		return new JsonResponse(['token' => $token]);
	}
}