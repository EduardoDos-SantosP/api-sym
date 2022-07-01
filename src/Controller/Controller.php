<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    private const JSON_RESPONSE_CONFIG = JsonResponse::DEFAULT_ENCODING_OPTIONS |
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR;

    protected static function createResponse(mixed $data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        $response->setEncodingOptions(self::JSON_RESPONSE_CONFIG);
        return $response;
    }
}