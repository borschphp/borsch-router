<?php

namespace Borsch\Router\Loader;

use ReflectionAttribute;
use Borsch\Router\Attribute\{Route as RouteAttribute, Controller};
use Borsch\Router\Route;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use function array_diff, array_filter, get_declared_classes, realpath;

class AttributeRouteLoader
{

    /** @var Route[] */
    private array $routes = [];

    public function __construct(
        /** @var string[] */
        private readonly array $directories,
        private readonly ContainerInterface $container
    ) {}

    public function load(): void
    {
        $finder = new Finder();
        $finder->files()->in($this->directories)->name('*.php');

        foreach ($finder as $file) {
            $file_path = $file->getRealPath();
            $classes = $this->loadAndFindDeclaredClasses($file_path);

            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);

                foreach ($ref->getAttributes(Controller::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                    $controller = $attribute->newInstance();

                    foreach ($ref->getMethods() as $method) {
                        foreach ($method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attr) {
                            $route = $attr->newInstance();
                            $this->routes[] = new Route(
                                $route->methods,
                                $controller->base_path . $route->path,
                                $this->container->get($class),
                                $route->name ?? null
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array<class-string>
     * @throws ReflectionException
     */
    private function loadAndFindDeclaredClasses(string $file): array
    {
        $before = get_declared_classes();
        require_once $file;
        $after = get_declared_classes();

        return array_filter(
            array_diff($after, $before),
            fn($c) => (new ReflectionClass($c))->getFileName() === realpath($file)
        );
    }

    /**
     * Returns an array of routes loaded from attributes.
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $routes = $this->routes;

        usort($routes, fn(Route $a, Route $b) => $a->getPriority() <=> $b->getPriority());

        return $routes;
    }
}
