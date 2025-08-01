<?php

namespace Borsch\Router\Loader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use Borsch\Router\Attribute\{Route as RouteAttribute, Controller};
use Borsch\Router\Route;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use function array_diff, array_filter, array_merge, get_declared_classes, realpath;

class AttributeRouteLoader
{

    /** @var Route[] */
    private array $routes = [];

    public function __construct(
        /** @var string[] */
        private readonly array $directories,
        private readonly ContainerInterface $container
    ) {}

    /**
     * @throws ReflectionException
     */
    public function load(): self
    {
        $files = [];
        foreach ($this->directories as $directory) {
            $files = array_merge($files, $this->findPhpFiles($directory));
        }

        foreach ($files as $file) {
            $classes = $this->loadAndFindDeclaredClasses($file);

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
                                new LazyRequestHandler($class, $this->container),
                                $route->name ?? null,
                                $route->priority ?? null
                            );
                        }
                    }
                }
            }
        }

        return $this;
    }

    /** @return string[] */
    private function findPhpFiles(string $directory): array
    {
        $results = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = $file->getRealPath();
            }
        }

        return $results;
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
