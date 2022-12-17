<?php

namespace App\Controller;

use App\Annotation\Routing\DevRoute;
use App\Annotation\Routing\NotAuthenticate;
use App\Annotation\Routing\NotRouted;
use App\Annotation\Routing\RouteOptions;
use App\Helper\MetaHelper;
use DirectoryIterator;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Yaml\Yaml;
use TypeError;
use function Symfony\Component\String\b;

class AppController extends Controller
{
	public readonly string $projectDir;
	
	public function __construct(ManagerRegistry $manager, ContainerBagInterface $bag)
	{
		parent::__construct($manager, new Serializer());
		$projectDirResourceName = 'kernel.project_dir';
		try {
			$this->projectDir = $bag->get($projectDirResourceName);
		} catch (ContainerExceptionInterface $e) {
			throw new RuntimeException("Falha ao encontrar o recurso '$projectDirResourceName' no container!");
		}
	}
	
	#[DevRoute]
	#[RouteOptions(path: '/')]
	public function loadRoutes(): Response
	{
		$this->doLoadRoutes();
		return $this->json('Rotas geradas com sucesso!');
	}
	
	public function doLoadRoutes(): void
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
		$yamlMap = ['when@dev' => $devRoutes->all(), ...$otherRoutes];
		
		$yamlStr = Yaml::dump($yamlMap, PHP_INT_MAX);
		file_put_contents($this->getRoutesFile(), $yamlStr);
	}
	
	public function getControllers(): Collection
	{
		return (new LazyCollection(fn() => new DirectoryIterator(__DIR__)))
			->filter(fn(DirectoryIterator $item) => $item->getExtension() == 'php')
			->map(fn(DirectoryIterator $item) => b($item->getFilename())
				->trimSuffix('.php')->prepend(__NAMESPACE__ . '\\')
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
				$routeName = self::getShortName($controllerClass)->append('_' . $m->name)->lower();
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
	
	public function getRoutesFile(): string
	{
		$routesPath = "$this->projectDir/config/routes.yaml";
		if (!file_exists($routesPath)) throw new RuntimeException("O arquivo de rotas $routesPath não foi encontrado!");
		return $routesPath;
	}
	
	private function isActionReturnType(?ReflectionType $type): bool
	{
		if ($type === null) return false;
		
		/** @var ReflectionNamedType[] $types */
		$types = is_a($type, ReflectionNamedType::class) ? [$type] : $type->getTypes();
		
		return collect($types)->every(
			fn(ReflectionNamedType $t) => class_exists($typeName = $t->getName()) && !$t->allowsNull() &&
				in_array(Response::class, [$typeName, ...class_parents($typeName)])
		);
	}
}