<?php

namespace App\Controller;

use App\Annotation\Routing\Permission;
use App\Bo\UsuarioPermissoesBo;
use App\Entity\Sessao;
use App\Entity\Usuario;
use App\Entity\UsuarioPermissoes;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use function Symfony\Component\String\b;

#[AsRoutingConditionService('authenticator')]
class RouteAuthenticator
{
	private const ALGORITHM = 'HS256';
	private const TOKEN_PREFIX = 'Bearer ';
	
	public function __construct(
		private readonly string $jwtSecret,
		private readonly RouterInterface $router,
		private readonly UsuarioPermissoesBo $permissoesBo
	) {}
	
	public function authenticate(Request $request): bool
	{
		if ($_ENV['APP_DEBUG'] && !$request->query->getBoolean('authenticate'))
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
		
		return $this->checkPermission($request, new Usuario(id: $decoded->usr));
	}
	
	private function checkPermission(Request $request, Usuario $usuario): bool
	{
		/** @var Route $route */
		$route = collect($this->router->getRouteCollection())
			->first(fn(Route $r) => $r->getPath() === $request->getPathInfo());
		
		[$controller, $action] = explode('::', $route->getDefault('_controller'));
		
		$permissaoRequerida = (new ReflectionClass($controller))
				->getMethod($action)
				->getAttributes(Permission::class)[0] ?? null;
		
		if (!$permissaoRequerida) return true;
		
		/** @var UsuarioPermissoes $permissoes */
		$permissoes = $this->permissoesBo->findByUsuario($usuario);
		
		if (!$permissoes) return false;
		
		/** @var Permission $permissao */
		$permissao = $permissaoRequerida->newInstance();
		
		return !!$permissoes->buscarPermissao($permissao->name);
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