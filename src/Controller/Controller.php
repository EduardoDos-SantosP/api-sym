<?php

namespace App\Controller;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\b;

abstract class Controller extends AbstractController
{
    private const JSON_RESPONSE_CONFIG = JsonResponse::DEFAULT_ENCODING_OPTIONS |
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;

    protected function json(
        mixed $data,
        int   $status = Response::HTTP_OK,
        array $headers = [],
        array $context = [],
        bool $uncapsuleObj = true): JsonResponse
    {
        if ($uncapsuleObj && is_object($data))
            $data = $this->uncapsuleObj($data);
        $response = new JsonResponse($data, $status, $headers);
        $response->setEncodingOptions(self::JSON_RESPONSE_CONFIG);
        return $response;
    }

    protected function uncapsuleObj(object $e): array
    {
        return collect((new ReflectionClass($e))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionMethod $m) => ($n = b($m->name))
                ->equalsTo($n = $n->trimPrefix('get'))
                ? [0 => 0] : [(string)$n->camel() => $e->{"get$n"}()]
            )->filter()->all();
    }
}