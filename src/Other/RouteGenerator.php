<?php

namespace App\Other;

use App\Annotation\Routing\DevRoute;
use App\Annotation\Routing\NotAuthenticate;
use App\Annotation\Routing\NotRouted;
use App\Annotation\Routing\RouteOptions;
use App\Controller\Controller;
use App\Helper\MetaHelper;
use DirectoryIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use TypeError;
use function Symfony\Component\String\b;

class RouteGenerator
{
	private const CONTROLLER_NAMESPACE = 'App\\Controller';
	private const DEV_ROUTES_YAML_KEY = 'when@dev';
	
	private readonly string $projectDir;
	private readonly string $yamlRoutesFile;
	private readonly string $permissionsWriteFile;
	private readonly string $permissionsReadFile;
	
	public function __construct()
	{
		$projectDir = __DIR__;
		while (!file_exists($projectDir . '/src'))
			$projectDir = dirname($projectDir);
		
		$this->projectDir = $projectDir;
		$this->yamlRoutesFile = "$projectDir/config/routes.yaml";
		collect([$this->projectDir, $this->yamlRoutesFile])->each(function ($path) {
			if (!file_exists($path))
				throw new FileNotFoundException("Não foi possível localizar o arquivo ou diretório '$path'!");
		});
		$this->permissionsWriteFile = "$projectDir/config/permissions/write.php";
		$this->permissionsReadFile = "$projectDir/config/permissions/read.php";
	}
	
	public function loadRoutes(): void
	{
		[$otherRoutes, $devRoutes] = $this->getControllers()
			->lazy()
			->flatMap(fn(string $controller) => $this->getControllerRoutes($controller))
			->groupBy('isdev')
			->map(fn(Collection $group) => $group->mapWithKeys(function (array $route) {
				$key = $route['name'];
				unset($route['name']);
				unset($route['isdev']);
				return [$key => $route];
			}))->all();
		$yamlMap = [self::DEV_ROUTES_YAML_KEY => $devRoutes->all(), ...$otherRoutes];
		
		$yamlStr = Yaml::dump($yamlMap, PHP_INT_MAX);
		file_put_contents($this->yamlRoutesFile, $yamlStr);
		
		$this->loadPermissions();
	}
	
	public function getControllers(): Collection
	{
		$controllerDir = $this->projectDir . '/src/Controller';
		return (new LazyCollection(fn() => new DirectoryIterator($controllerDir)))
			->filter(fn(DirectoryIterator $item) => $item->getExtension() == 'php')
			->map(fn(DirectoryIterator $item) => b($item->getFilename())
				->trimSuffix('.php')->prepend(self::CONTROLLER_NAMESPACE . '\\')
			)
			->filter(function (string $className) {
				if (!class_exists($className)) return false;
				$class = new ReflectionClass($className);
				return !$class->isAbstract()
					&& $class->isSubclassOf(AbstractController::class)
					&& !$class->getAttributes(NotRouted::class);
			})
			->collect();
	}
	
	private function getControllerRoutes(string $controllerClass): array
	{
		return MetaHelper::getPublicMethods($controllerClass)
			->lazy()
			->filter(
				fn(ReflectionMethod $m) => $this->isResponseType($m->getReturnType()) &&
					!MetaHelper::getAttribute($m, NotRouted::class)
			)
			->map(function (ReflectionMethod $m) use ($controllerClass) {
				$routeName = Controller::getShortName($controllerClass)->append('_' . $m->name)->lower();
				/** @var ?RouteOptions $routeOptions */
				$routeOptions = MetaHelper::getAttribute($m, RouteOptions::class)?->newInstance();
				return [
					'name' => $routeName->toString(),
					
					'isdev' => !!MetaHelper::getAttribute($m, DevRoute::class),
					
					'path' => b($routeOptions?->path ?? $routeName->replace('_', '/'))->ensureStart('/') .
						($routeOptions?->toUri() ?? ''),
					
					'controller' => "$controllerClass::$m->name",
					
					...collect(['requirements', 'defaults'])
						->mapWithKeys(fn($p) => [$p => $routeOptions?->$p])
						->filter()->all(),
					
					...collect([NotAuthenticate::class, DevRoute::class])
						->some(fn(string $class) => MetaHelper::getAttribute($m, $class))
						? [] : ['condition' => 'service("authenticator").authenticate(request)']
				];
			})->all();
	}
	
	private function isResponseType(?ReflectionType $type): bool
	{
		if (!$type) return false;
		if (is_a($type, ReflectionNamedType::class))
			return $type->getName() == Response::class ||
				is_subclass_of($type->getName(), Response::class);
		
		if (is_a($type, ReflectionUnionType::class))
			return collect($type->getTypes())->every(self::isResponseType(...));
		if (is_a($type, ReflectionIntersectionType::class))
			return collect($type->getTypes())->some(self::isResponseType(...));
		
		throw new TypeError(
			"O tipo $type não é reconhecido como um subtipo de " . ReflectionType::class . '!'
		);
	}
	
	public function loadPermissions(): void
	{
		$routes = $this->getRoutesFromYamlFile();
	}
	
	private function getRoutesFromYamlFile(): array
	{
		/** @var array $rawArray */
		$rawArray = Yaml::parseFile($this->yamlRoutesFile);
		return (new LazyCollection($rawArray))
			->filter(fn($_, $key) => $key !== self::DEV_ROUTES_YAML_KEY)
			->map(function (array $value, string $key) {
				[$controller, $method] = explode('::', $value['controller']);
				return (object)[
					'controller' => $controller,
					'method' => $method
				];
			})
			->all();
	}

//	private function getRoutesFromYamlFile(): array
//	{
//		/** @var array $rawArray */
//		$rawArray = Yaml::parseFile($this->yamlRoutesFile);
//		return (new LazyCollection($rawArray))
//			->filter(fn($value, $key) => $key !== self::DEV_ROUTES_YAML_KEY)
//			->map(function (array $value, string $key) {
//				[$controller, $method] = explode('::', $value['controller']);
//				return (object)[
//					'route' => $key,
//					'controller' => $controller,
//					'method' => $method
//				];
//			})
//			->all();
//	}
//
//	private function createPermissionsFilesIfDontExist(): void
//	{
//		foreach ([$this->permissionsWriteFile, $this->permissionsReadFile] as $file) {
//			if (file_exists($file)) continue;
//			$path = dirname($file);
//			if (!is_dir($path)) mkdir($path, recursive: true);
//			file_put_contents(
//				$file,
//				match ($file) {
//					$this->permissionsWriteFile => "<?php\n\n//usings\n//endusings\n\nreturn [];\n",
//					$this->permissionsReadFile => "<?php\n\nreturn [];\n"
//				}
//			);
//		}
//	}
//
//	private function updatePermissionsReadFile(): void
//	{
//		/** @var Closure[] $permissions */
//		$permissions = require $this->permissionsWriteFile;
//		$permissionsFileArray = file($this->permissionsWriteFile);
//		collect($permissions)
//			->map(function (Closure $closure) use ($permissionsFileArray) {
//				$reflection = new ReflectionFunction($closure);
//				$controller = $reflection->getParameters()[0]->getType()->getName();
//				$lineMethod = $permissionsFileArray[$reflection->getStartLine() - 1];
//				preg_match('/(\d) => .*->([\dA-z]+)\(\.\.\.\)/', $lineMethod, $matches);
//				[, $permissionId, $method] = $matches;
//
//				return [
//					'id' => +$permissionId,
//					'controller' => $controller,
//					'method' => $method
//				];
//			})->dump();
//	}
}
/**
 * Obter as rotas do arquivo Yaml                                   (): array<int, { route, controller, method }> -> $a
 * Criar os aqruivos de permissão se não existirem                  (): void
 * Atualizar o arquivo de Leitura a partir do arquivo de Escrita    (): array<string, array<string, int>> -> $b
 * Persistir as permissões a partir do arquivo de Leitura           ($a, $b): array<int, Permission> -> $c
 * Atualizar o arquivo de Escrita                                   ($c): void
 */