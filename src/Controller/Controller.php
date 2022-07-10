<?php

namespace App\Controller;

use App\EntityServiceTrait;
use App\Facade\EntityFacade;
use App\Facade\IFacade;
use App\Helper\Singleton;
use App\IEntityService;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\b;

abstract class Controller extends AbstractController implements IEntityService
{
    use EntityServiceTrait;

    private const JSON_RESPONSE_CONFIG = JsonResponse::DEFAULT_ENCODING_OPTIONS |
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;

    private static ?EntityFacade $facade = null;

    private static ManagerRegistry $manager;

    public function __construct(ManagerRegistry $manager)
    {
        self::$manager = $manager;
    }

    protected static function getFacade(): EntityFacade
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Singleton::getInstance(
            'controller_facade',
            fn() => self::findByEntity(self::getModelName(), 'facade', self::$manager)
        );
    }

    protected function json(
        mixed $data,
        int   $status = Response::HTTP_OK,
        array $headers = [],
        array $context = [],
        bool  $uncapsuleObj = true): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->setEncodingOptions(self::JSON_RESPONSE_CONFIG);
        return $response;
    }

    protected function uncapsuleObj(mixed $o): mixed
    {
        return match (true) {
            is_iterable($o) => collect($o)->map($this->uncapsuleObj(...)),
            is_object($o) => collect((new ReflectionClass($o))->getMethods(ReflectionMethod::IS_PUBLIC))
                ->mapWithKeys(fn(ReflectionMethod $m) => ($methodName = b($m->name))
                    ->equalsTo($propName = $methodName->trimPrefix('get'))
                    ? [0 => null] : [(string)$propName->camel() => $o->{"get$propName"}()]
                )->filter()->all(),
            default => $o
        };
    }
}