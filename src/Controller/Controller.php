<?php

namespace App\Controller;

use App\Helper\MetaHelper;
use ReflectionMethod;
use ReflectionObject;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\ByteString;
use Throwable;
use function Symfony\Component\String\b;

abstract class Controller extends AbstractController
{
	private const JSON_RESPONSE_CONFIG = JsonResponse::DEFAULT_ENCODING_OPTIONS |
	JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;
	
	private SerializerInterface $serializer;
	
	public function __construct(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}
	
	public static function getShortName(string $controllerClass = null): ByteString
	{
		return b(self::getName($controllerClass))->afterLast('\\');
	}
	
	public static function getName(string $controllerClass = null): ByteString
	{
		return b($controllerClass ?? static::class)->trimSuffix('Controller');
	}
	
	protected function json(
		mixed $data,
		int $status = Response::HTTP_OK,
		array $headers = [],
		array $context = [],
		bool $uncapsuleObj = true
	): JsonResponse {
		$response = new JsonResponse($data, $status, $headers);
		$response->setEncodingOptions(self::JSON_RESPONSE_CONFIG);
		return $response;
	}
	
	protected function uncapsuleObj(mixed $obj): mixed
	{
		return match (true) {
			is_iterable($obj) => collect($obj)->map($this->uncapsuleObj(...)),
			is_object($obj) => MetaHelper::getPublicMethods(new ReflectionObject($obj))
				->mapWithKeys(fn(ReflectionMethod $m) => ($methodName = b($m->name))
					->equalsTo($propName = $methodName->trimPrefix('get'))
					? [0 => null] : [(string)$propName->camel() => $obj->{"get$propName"}()]
				)->filter()->all(),
			default => $obj
		};
	}
	
	protected function deserialize(Request|string $requestOrJson, string $class): mixed
	{
		$json = is_string($requestOrJson)
			? $requestOrJson
			: $requestOrJson->getContent();
		try {
			return $class
				? $this->serializer->deserialize($json, $class, 'json')
				: json_decode($json);
		} catch (Throwable $e) {
			throw new RuntimeException(
				message: 'Não foi possível desserializar a requisição'
				. ($class ? " como um objeto de '$class'" : '') . '!',
				previous: $e
			);
		}
	}
}