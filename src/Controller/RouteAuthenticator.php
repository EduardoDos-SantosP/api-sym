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
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Throwable;
use function Symfony\Component\String\b;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

#[AsRoutingConditionService('authenticator')]
class RouteAuthenticator
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_PREFIX = 'Bearer ';

    public function __construct(
        private readonly string $jwtSecret,
        private readonly RouterInterface $router,
        private readonly UsuarioPermissoesBo $permissoesBo,
        private readonly LoggerInterface $logger
    )
    {
    }

    private function log(string $message): void
    {
        $this->logger->info($message);
    }

    public function authenticate(Request $request, array $params): bool
    {
        $this->log('starting authentication');
        // if ($_ENV['APP_ENV'] == 'dev' && !$request->query->getBoolean('authenticate'))
        //     return true;
        $this->log('checking authorization header');

        $dirtyToken = $request->headers?->get('authorization');
        if (!$dirtyToken) return false;
        $token = preg_replace('/^' . self::TOKEN_PREFIX . '/i', '', trim($dirtyToken), 1);

        $this->log('decoding token');

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, self::ALGORITHM));
            $this->log('token decoded successfully');
        } catch (Throwable $e) {
            $errClass = get_class($e);
            throw new BadRequestException(
                "Não foi possível desserializar o token de autenticação ($errClass): " . $e->getMessage(),
                previous: $e
            );
        }

        return $this->checkPermission($params, new Usuario(id: $decoded->usr));
    }

    private function checkPermission(array $params, Usuario $usuario): bool
    {
        [$controller, $action] = explode('::', $params['_controller']);

        $permissaoRequerida = (new ReflectionClass($controller))
            ->getMethod($action)
            ->getAttributes(Permission::class)[0] ?? null;

        $this->log('checking permission for user');

        if (!$permissaoRequerida) return true;

        /** @var UsuarioPermissoes $permissoes */
        $permissoes = $this->permissoesBo->findByUsuario($usuario);

        $this->log('user permissions found: ' . ($permissoes ? 'yes' : 'no'));

        if (!$permissoes) return false;

        /** @var Permission $permissao */
        $permissao = $permissaoRequerida->newInstance();

        $this->log('checking if user has permission: ' . $permissao->name);

        return !!$permissoes->buscarPermissao($permissao->name)
            ?? throw new UnauthorizedHttpException(
                $permissaoRequerida->name,
                'Você não possui permissão',
                headers: [
                    'Access-Control-Allow-Origin' => '*'
                ]
            );
    }

    public function generateTokenedResponse(Sessao $sessao): Response
    {
        if (!$sessao->getUsuario()?->getId())
            throw new InvalidArgumentException('A sessão precisa ter um usuário com id!');

        $this->log('Login realizado para o usuário: ' . $sessao->getUsuario()->getId());

        $token = b(JWT::encode($sessao->tokenize(), $this->jwtSecret, self::ALGORITHM))
            ->ensureStart(self::TOKEN_PREFIX)
            ->toString();

        return new JsonResponse(['token' => $token]);
    }
}