<?php
/**
 * @author debuss-a
 */

namespace Borsch\Router;

use FastRoute\{
    Dispatcher,
    RouteCollector,
    RouteParser\Std
};
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use function FastRoute\{
    cachedDispatcher,
    simpleDispatcher
};

/**
 * Class FastRouteRouter
 * @package Borsch\Router
 */
class FastRouteRouter implements RouterInterface
{

    /**
     * @param RouteInterface[] $routes
     * @param string|null $cache_file
     * @param bool $cache_disabled
     */
    public function __construct(
        /** @var RouteInterface[]  */
        protected array $routes = [],
        protected ?string $cache_file = null,
        protected bool $cache_disabled = false
    ) {}

    /**
     * @return null|string
     */
    public function getCacheFile(): ?string
    {
        return $this->cache_file;
    }

    /**
     * @param string $cache_file
     */
    public function setCacheFile(string $cache_file): void
    {
        $this->cache_file = $cache_file;
    }

    /**
     * @return bool
     */
    public function isCacheDisabled(): bool
    {
        return $this->cache_disabled;
    }

    /**
     * @param bool $cache_disabled
     */
    public function setCacheDisabled(bool $cache_disabled): void
    {
        $this->cache_disabled = $cache_disabled;
    }

    /**
     * @param callable $callable
     * @return Dispatcher
     */
    protected function getDispatcher(callable $callable): Dispatcher
    {
        if (is_string($this->cache_file)) {
            return cachedDispatcher($callable, [
                'cacheFile' => $this->cache_file,
                'cacheDisabled' => $this->cache_disabled
            ]);
        }

        return simpleDispatcher($callable);
    }

    /**
     * @inheritDoc
     */
    public function addRoute(RouteInterface $route): void
    {
        if (isset($this->routes[$route->getName()])) {
            throw new InvalidArgumentException(sprintf(
                'A similar route name (%s) has already been provided.',
                $route->getName()
            ));
        }

        $this->routes[$route->getName()] = $route;
    }

    /**
     * @inheritDoc
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @inheritDoc
     */
    public function match(ServerRequestInterface $request): RouteResultInterface
    {
        $dispatcher = $this->getDispatcher(function(RouteCollector $collector) {
            foreach ($this->routes as $route) {
                $collector->addRoute($route->getAllowedMethods(), $route->getPath(), $route->getName());
            }
        });

        $route_info = $dispatcher->dispatch(
            $request->getMethod(),
            rawurldecode($request->getUri()->getPath())
        );

        if ($route_info[0] == Dispatcher::FOUND) {
            return RouteResult::fromRouteSuccess($this->routes[$route_info[1]], $route_info[2]);
        }

        return RouteResult::fromRouteFailure(
            $route_info[0] == Dispatcher::METHOD_NOT_ALLOWED ?
                $route_info[1] : []
        );
    }

    /**
     * @param string $name
     * @param array $substitutions
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function generateUri(string $name, array $substitutions = []): string
    {
        if (!isset($this->routes[$name])) {
            throw new InvalidArgumentException(sprintf(
                'The route named %s is unknown...',
                $name
            ));
        }

        // Reverse the array so we start by the longest possible URI.
        $routes = array_reverse((new Std())->parse($this->routes[$name]->getPath()));

        foreach ($routes as $parts) {
            if (!$this->routeCanBeGenerated($parts, $substitutions)) {
                continue;
            }

            return $this->buildUri($parts, $substitutions);
        }

        throw new RuntimeException(sprintf(
            'Unable to generate URI "%s", missing substitutions, only received : %s...',
            $this->routes[$name]->getPath(),
            implode(', ', $substitutions)
        ));
    }

    /**
     * @param array $parts
     * @param array $substitutions
     * @return bool
     */
    protected function routeCanBeGenerated(array $parts, array $substitutions): bool
    {
        foreach ($parts as $part) {
            if (is_string($part)) {
                continue;
            }

            if (!isset($substitutions[$part[0]])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $parts
     * @param array $substitutions
     * @return string
     * @throws RuntimeException
     */
    protected function buildUri(array $parts, array $substitutions): string
    {
        $uri = '';
        foreach ($parts as $part) {
            if (is_string($part)) {
                $uri .= $part;
                continue;
            }

            if (!preg_match('#^'.$part[1].'$#', (string)$substitutions[$part[0]])) {
                throw new RuntimeException(sprintf(
                    'Given substitution for "%s" (= %s) does not match the route constraint "%s"...',
                    $part[0],
                    (string)$substitutions[$part[0]],
                    $part[1]
                ));
            }

            $uri .= $substitutions[$part[0]];
        }

        return $uri;
    }
}