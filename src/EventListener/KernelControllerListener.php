<?php

namespace App\EventListener;

use App\Annotation\Routing\NotAuthenticate;
use Psr\Log\LoggerAwareInterface;
use ReflectionMethod;
use Throwable;
use ReflectionClass;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\Usuario;
use Psr\Log\LoggerInterface;
use App\Controller\Controller;
use App\Bo\UsuarioPermissoesBo;
use App\Entity\UsuarioPermissoes;
use App\Annotation\Routing\NotRouted;
use App\Annotation\Routing\Permission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class KernelControllerListener implements LoggerAwareInterface //implements EventSubscriberInterface
{
    private const ALGORITHM = 'HS256';
    private const TOKEN_PREFIX = 'Bearer ';

    public function __construct(
        private readonly string $jwtSecret,
        private readonly UsuarioPermissoesBo $permissoesBo,
        private LoggerInterface $logger
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $rawController = $event->getController();
        $this->logger->info('onKernelController');

        /** @var Controller $controller
         *  @var string $action */
        [$controller, $action] = is_array($rawController) ? $rawController : [$rawController, '__invoke'];

        $isRoutedController = !(new ReflectionClass($controller))->getAttributes(NotRouted::class);

        $this->logger->info("$action is routed: " . ($isRoutedController ? 'true' : 'false'));
        if (!$isRoutedController)
            return;

        $isValid = $this->authenticate($event->getRequest(), $controller, $action);
        if (!$isValid)
            throw new AccessDeniedHttpException(
                'Você não possui permissão',
                headers: [
                    'Access-Control-Allow-Origin' => '*'
                ]
            );
    }

    private function authenticate(Request $request, Controller $controller, string $action): bool
    {
        $isToAutheticate = !(new ReflectionMethod($controller, $action))
            ->getAttributes(NotAuthenticate::class);
        
        if (!$isToAutheticate) return true;

        $dirtyToken = $request->headers?->get('authorization');
        if (!$dirtyToken)
            return false;
        $token = preg_replace('/^' . self::TOKEN_PREFIX . '/i', '', trim($dirtyToken), 1);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, self::ALGORITHM));
        } catch (Throwable $e) {
            $errClass = get_class($e);
            throw new BadRequestHttpException(
                "Não foi possível desserializar o token de autenticação ($errClass): " . $e->getMessage(),
                $e
            );
        }

        return $this->checkPermission(new Usuario(id: $decoded->usr), $controller, $action);
    }

    private function checkPermission(Usuario $usuario, Controller $controller, string $action): bool
    {
        $this->logger->info('checkPermission for user ' . $usuario->getId());
        $permissaoRequerida = (new ReflectionClass($controller))
            ->getMethod($action)
            ->getAttributes(Permission::class)[0] ?? null;

        if (!$permissaoRequerida)
            return true;

        /** @var UsuarioPermissoes $permissoes */
        $permissoes = $this->permissoesBo->findByUsuario($usuario);

        $this->logger->info(
            'checking permission for user ' . $usuario->getId() . '. Total: '
            . count($permissoes?->getPermissoes() ?? [])
        );

        if (!$permissoes)
            return false;
        
        /** @var Permission $permissao */
        $permissao = $permissaoRequerida->newInstance();

        $this->logger->info('permissaoRequerida: ' . $permissao->name);

        return !!$permissoes->buscarPermissao($permissao->name);
    }
}