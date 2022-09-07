<?php

namespace App\Controller;

use App\Annotation\Routing\NotRouted;
use App\Annotation\Routing\RouteParams;
use App\Helper\ReflectionHelper;
use DirectoryIterator;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\String\b;

class AppController extends Controller
{
    public readonly string $projectDir;

    public function __construct(ManagerRegistry $manager, ContainerBagInterface $bag)
    {
        parent::__construct($manager);
        $this->projectDir = $bag->get('kernel.project_dir');
    }

    public function loadRoutes(ManagerRegistry $bag): Response|null
    {
        $this->autoLoadRoutes();
        return $this->json('Rotas geradas com sucesso!');
    }

    public function autoLoadRoutes(): void
    {
        $thisRoute = __CLASS__ . '::' . (new ReflectionFunction($this->loadRoutes(...)))->name;

        $routes = [];
        /** @var $controller string */
        foreach ($this->getControllers() as $controller)
            foreach ($this->getControllerRoutes($controller) as $key => $route)
                if ($route['controller'] !== $thisRoute)
                    $routes[$key] = $route;
                else if (isset($routes['when@dev']))
                    $routes['when@dev'][$key] = $route;
                else
                    $routes['when@dev'] = [$key => $route];


        $yamlStr = Yaml::dump($routes, PHP_INT_MAX);
        file_put_contents($this->getProjectDir(), $yamlStr);
    }

    public function getControllers(): Collection
    {
        $controllers = new Collection();
        $dirIterator = new DirectoryIterator(__DIR__);
        foreach ($dirIterator as $item)
            if (
                //É arquivo
                $item->isFile() &&
                //Possui extensão php
                !($file = b($item->getFilename()))
                    ->equalsTo($file = $file->trimEnd('.php')) &&
                //Existe a classe com o nome do arquivo
                class_exists($className = (string)$file->prepend(__NAMESPACE__ . '\\')) &&
                //Não é classe abstrata
                !str_contains(file_get_contents(__DIR__ . "/$file.php"), "abstract class $file") &&
                //Não usa a annotation NotRouted
                !(new ReflectionHelper($className))->getAttrFromClass(NotRouted::class) &&
                //Extende de AbstractController
                is_subclass_of($className, AbstractController::class)
            ) $controllers[] = $className;

        return $controllers;
    }

    private function getControllerRoutes(string $controller): array
    {
        $setRootRoute = $controller === self::class;

        $getPublicMethods =
            fn(string $contollerName): array => (new ReflectionClass($contollerName))
                ->getMethods(ReflectionMethod::IS_PUBLIC);
        $actions = array_filter(
            $getPublicMethods($controller),
            fn(ReflectionMethod $m) => !in_array($m, $getPublicMethods(Controller::class))
        );

        $controller = b($controller)->beforeLast('Controller');

        $normalizeRoute = fn(string $name): string => b($name)->afterLast('\\')->lower();
        $nameRoute = fn(string $action) => $normalizeRoute($controller . '_' . $action);

        $loadRoutesMethodName = (new ReflectionFunction($this->loadRoutes(...)))->name;
        $rootNameRoute = $nameRoute($loadRoutesMethodName);
        $rootPath = '/' . $normalizeRoute("$controller/$loadRoutesMethodName");

        $yamlMap = [];
        /** @var ReflectionMethod $action */
        foreach ($actions as $action) {
            if (!$this->isActionReturnType($action->getReturnType())) continue;

            $attributes = collect($action->getAttributes());

            //TODO: Tornar essa função global
            $getAttr = fn(string $class): ?ReflectionAttribute => $attributes->first(
                fn(ReflectionAttribute $a) => $a->getName() === $class
            );

            if ($getAttr(NotRouted::class)) continue;

            /** @var $params ?RouteParams */
            $params = $getAttr(RouteParams::class)?->newInstance();

            $yamlMap[$nameRoute($action = $action->name)] = [
                'path' => '/' . $normalizeRoute("$controller/$action") . $params?->toUri(),
                'controller' => $controller . "Controller::$action",
                ...collect(['requirements', 'defaults'])
                    ->mapWithKeys(fn($p) => [$p => $params?->$p])->filter()->all()
            ];
        }
        if ($setRootRoute)
            $yamlMap[$rootNameRoute]['path'] = ($path = $yamlMap[$rootNameRoute]['path']) === $rootPath ? '/' : $path;

        return $yamlMap;
    }

    private function isActionReturnType(?ReflectionType $type): bool
    {
        if ($type === null) return false;

        /** @var ReflectionNamedType[] $types */
        $types = is_a($type, ReflectionNamedType::class) ? [$type] : $type->getTypes();

        return collect($types)->every(
            fn(ReflectionNamedType $t) => class_exists($typeName = $t->getName()) &&
                !$t->allowsNull() &&
                in_array(Response::class, [$typeName, ...class_parents($typeName)])
        );
    }

    public function getProjectDir(): string
    {
        $routesPath = ($this->projectDir ?? null) . '/config/routes.yaml';
        if (!file_exists($routesPath))
            throw new RuntimeException("O arquivo de rotas $routesPath não foi encontrado!");
        return $routesPath;
    }
}