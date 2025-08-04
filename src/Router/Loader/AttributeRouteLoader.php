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
        private readonly ContainerInterface $container,
        private readonly ?string $cache_file = null,
        private readonly bool $cache_disabled = false
    ) {}

    /**
     * @throws ReflectionException
     */
    public function load(): self
    {
        if ($this->cache_file && !$this->cache_disabled && file_exists($this->cache_file)) {
            $this->uncacheRoute();

            if (count($this->routes)) {
                return $this;
            }
        }

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

        if ($this->cache_file && !$this->cache_disabled && count($this->routes)) {
            $this->cacheRoutes();
        }

        return $this;
    }

    private function cacheRoutes(): void
    {
        $cache = [];
        foreach ($this->routes as $route) {
            /** @var LazyRequestHandler $handler */
            $handler = $route->getHandler();

            $cache[] = [
                'methods' => $route->getAllowedMethods(),
                'path' => $route->getPath(),
                'handler' => $handler->id,
                'name' => $route->getName(),
                'priority' => $route->getPriority()
            ];
        }

        file_put_contents($this->cache_file, '<?php return '.var_export($cache, true). ';');
    }

    private function uncacheRoute(): void
    {
        $cached_routes = require $this->cache_file;
        if (!$cached_routes || !is_array($cached_routes) || !count($cached_routes)) {
            return;
        }

        foreach ($cached_routes as $route_data) {
            $this->routes[] = new Route(
                $route_data['methods'],
                $route_data['path'],
                new LazyRequestHandler($route_data['handler'], $this->container),
                $route_data['name'] ?? null,
                $route_data['priority'] ?? null
            );
        }
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
