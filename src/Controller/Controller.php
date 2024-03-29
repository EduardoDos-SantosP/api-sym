<?php

namespace App\Controller;

use App\EntityServiceTrait;
use App\Enum\EnumServiceType;
use App\Facade\EntityFacade;
use App\Helper\Singleton;
use App\IEntityService;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
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

    /**
     * @return ManagerRegistry
     */
    public static function getManager(): ManagerRegistry
    {
        return self::$manager;
    }

    protected static function getFacade(): EntityFacade
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Singleton::getInstance(
            'controller_facade',
            fn() => self::findByEntity(self::getModelName(), EnumServiceType::Facade, self::$manager)
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

    protected function deserialize(string $json, ?SerializerInterface $serializer = null, ?string $class = null): mixed
    {
        try {
            return $serializer && $class
                ? $serializer->deserialize($json, $class, 'json')
                : json_decode($json);
        } catch (Throwable $e) {
            throw new RuntimeException(
                message: 'Não foi possível desserializar a requisição'
                . ($class && " como um objeto de '$class'") . '!',
                previous: $e
            );
        }
    }
}