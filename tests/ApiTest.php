<?php

namespace App\Tests;

use App\Entity\Model;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\String\b;

class ApiTest extends AbstractCrudTest
{
    private function getEntityUri(): string
    {
        return '/' . b(self::$currentEntity)->afterLast('\\')->snake();
    }

    public function testCrud(): void
    {
        $hasController = $this->hasController();
        if ($hasController)
            parent::testCrud();
    }

    private function hasController(): bool
    {
        $parsed = Yaml::parseFile('./config/routes.yaml');
        foreach ($parsed as $name => $route) {
            if ($name == 'when@dev') continue;
            $url = $route['path'] ?? '';
            if (!$url) continue;
            $uri = b($url)->after('/')->before('/');
            if ($uri == $this->getEntityUri()) return true;
        }
        return false;
    }

    public function fetchCountEntities(): int
    {
        $route = $this->getEntityUri() . '/all';
        self::$client->request('GET', $route);

        $response = self::$client->getResponse();
        $json = $response->getContent();
        self::assertJson($json);

        $items = json_decode($json);
        self::assertIsArray($items);
        return count($items);
    }

    public function store(): int
    {
        $model = [];
        /** @var class-string<Model> $modelClass */
        $modelClass = self::$currentEntity;
        foreach ($modelClass::getProperties() as $prop) {
            if (!$prop->setter) continue;
            $testValue = match ($prop->type) {
                'int', 'float' => 10,
                'string' => 'Valor teste para ' . $prop->property,
                'bool' => false,
                'array' => [],
                default => null
            };
            if ($testValue !== null)
                $model[$prop->property] = $testValue;
        }
        $route = $this->getEntityUri() . '/new';
        self::$client->request('POST', $route, content: json_encode($model));

        $response = self::$client->getResponse();
        $json = $response->getContent();
        self::assertJson($json);
        $model = json_decode($json);

        self::assertGreaterThan(0, $model->id);

        return $model->id;
    }

    public function fetchById(int $id): object
    {
        self::$client->request(
            'POST',
            $this->getEntityUri() . "/byid",
            content: json_encode(['id' => $id])
        );
        $response = self::$client->getResponse();
        $json = $response->getContent();
        self::assertJson($json);

        $model = json_decode($json);
        self::assertGreaterThan(0, $model->id);
        return $model;
    }

    public function delete(object $model): void
    {
        self::$client->request(
            'POST',
            $this->getEntityUri() . "/delete",
            content: json_encode(['id' => $model->id])
        );
    }
}