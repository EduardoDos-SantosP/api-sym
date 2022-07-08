<?php

namespace App\Controller;

use App\Annotation\Routing\NotRouted;
use App\Annotation\Routing\RouteParams;
use App\Helper\ReflectionHelper;
use DirectoryIterator;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use function Symfony\Component\String\b;

#[Route('/app', 'app_')]
class AppController extends Controller
{
    public readonly string $projectDir;

    public function __construct(ContainerBagInterface $bag)
    {
        $this->projectDir = $bag->get('kernel.project_dir');
    }

    #[Route('/loadRoutes', 'load_routes')]
    public function loadRoutes(): Response|null
    {
        $this->autoLoadRoutes();
        return $this->json('Rotas geradas com sucesso!');
    }

    public function autoLoadRoutes(): void
    {
        $thisRoute = __CLASS__ . '::' . (new \ReflectionFunction($this->loadRoutes(...)))->name;

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

        $routesPath = $this->projectDir . '/config/routes.yaml';
        if (!file_exists($routesPath))
            throw new RuntimeException("O arquivo de rotas $routesPath não foi encontrado!");

        $yamlStr = Yaml::dump($routes, PHP_INT_MAX);
        file_put_contents($routesPath, $yamlStr);
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
        $actions = array_filter(
            ReflectionHelper::publicMethodsOf($controller),
            fn(ReflectionMethod $m) => !in_array($m, ReflectionHelper::publicMethodsOf(AbstractController::class))
        );

        $controller = b($controller)->beforeLast('Controller');

        $normalizeName = fn(string $name): string => b($name)->afterLast('\\')->lower();
        $nameRoute = fn(string $action) => $normalizeName($controller . '_' . $action);

        $yamlMap = new Collection();

        foreach ($actions as $action) {
            //Verifica se não retorna um Response
            if (
                !class_exists($className = $action->getReturnType()?->getName()) ||
                !in_array(Response::class, class_parents($className)) &&
                $className !== Response::class
            ) continue;

            $attributes = collect($action->getAttributes());

            $getAttr = fn(string $class): ?ReflectionAttribute => $attributes->first(
                fn(ReflectionAttribute $a) => $a->getName() === $class
            );

            if ($getAttr(NotRouted::class)) continue;

            /** @var $params ?RouteParams */
            $params = $getAttr(RouteParams::class)?->newInstance();

            $yamlMap[$nameRoute($action = $action->name)] = [
                'path' => '/' . $normalizeName("$controller/$action") . $params?->toUri(),
                'controller' => $controller . "Controller::$action",
                ...collect(['requirements', 'defaults'])
                    ->mapWithKeys(fn($p) => [$p => $params?->$p])->filter()->all()
            ];
        }

        return $yamlMap->all();
    }
}