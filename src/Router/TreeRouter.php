<?php

namespace Borsch\Router;

use Borsch\Router\Contract\{RouteInterface, RouteResultInterface, RouterInterface};
use Borsch\Router\Exception\{InvalidArgumentException, RuntimeException};
use Borsch\Router\Node\RouteNode;
use Borsch\Router\Result\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

class TreeRouter implements RouterInterface
{

    /** @var RouteInterface[]  */
    protected array $routes = [];
    private RouteNode $root;
    private bool $use_cache = false;

    /**
     * @param RouteInterface[] $routes
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $routes = [],
        protected ?string $cache_file = null,
        protected bool $cache_disabled = false
    ) {
        if (!$cache_disabled && $cache_file && file_exists($cache_file)) {
            $cache_data = file_get_contents($cache_file);
            if ($cache_data !== false) {
                $this->use_cache = true;
                $this->root = unserialize($cache_data);
            }
        } else {
            $this->root = new RouteNode('/');

            foreach ($routes as $route) {
                $this->addRoute($route);
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addRoute(RouteInterface $route): void
    {
        if ($this->use_cache) {
            return;
        }

        if (isset($this->routes[$route->getName()])) {
            throw InvalidArgumentException::routeNameAlreadyExists($route->getName());
        }

        $this->routes[$route->getName()] = $route;

        $path = trim($route->getPath(), '/');
        if (empty($path)) {
            $this->root->route = $route;
            return;
        }

        $segments = explode('/', $path);
        $node = $this->root;

        foreach ($segments as $segment) {
            if (preg_match('/^\{([^:}]+)(?::([^}]+))?}$/', $segment, $matches)) {
                $varName = $matches[1];
                $pattern = $matches[2] ?? '[^/]+';
                $node = $node->addDynamicChild($varName, $pattern);
            } else {
                $node = $node->addChild($segment);
            }
        }

        $node->route = $route;

        if (!$this->cache_disabled && $this->cache_file) {
            file_put_contents($this->cache_file, serialize($this->root));
        }
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function match(ServerRequestInterface $request): RouteResultInterface
    {
        $path = trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        $segments = empty($path) ? [] : explode('/', $path);
        $node = $this->root;
        $params = [];

        foreach ($segments as $segment) {
            $matched = false;

            // First check static routes (faster)
            foreach ($node->children as $child) {
                if (!$child->is_dynamic && $child->segment === $segment) {
                    $node = $child;
                    $matched = true;
                    break;
                }
            }

            // Then check dynamic routes if no static match found
            if (!$matched) {
                foreach ($node->children as $child) {
                    if ($child->is_dynamic && preg_match('/^' . $child->regex_pattern . '$/', $segment)) {
                        $params[$child->variable_name] = $segment;
                        $node = $child;
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched) {
                return RouteResult::fromRouteFailure([]);
            }
        }

        if (!$node->route) {
            return RouteResult::fromRouteFailure([]);
        }

        $allowed_methods = $node->route->getAllowedMethods();
        if (!in_array($method, $allowed_methods)) {
            return RouteResult::fromRouteFailure($allowed_methods);
        }

        return RouteResult::fromRouteSuccess($node->route, $params);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function generateUri(string $name, array $substitutions = []): string
    {
        if (!isset($this->routes[$name])) {
            throw InvalidArgumentException::routeNameIsUnknown($name);
        }

        $route = $this->routes[$name];
        $path = $route->getPath();

        // Replace dynamic parameters with values from substitutions
        if (preg_match_all('/\{([^:}]+)(?::[^}]+)?}/', $path, $matches)) {
            foreach ($matches[1] as $index => $key) {
                if (!isset($substitutions[$key])) {
                    throw RuntimeException::unableToGenerateUri($path, $substitutions);
                }

                // Extract pattern if it exists in the current parameter definition
                if (preg_match('/\{[^:}]+:([^}]+)}/', $matches[0][$index], $patternMatch)) {
                    $regexPattern = $patternMatch[1];
                    if (!preg_match('/^' . $regexPattern . '$/', (string)$substitutions[$key])) {
                        throw RuntimeException::substitutionDoesNotMatchRouteConstraint(
                            $key,
                            (string)$substitutions[$key],
                            $regexPattern
                        );
                    }
                }

                $pattern = sprintf('/\{%s(?::[^}]+)?\}/', preg_quote($key, '/'));
                $path = preg_replace($pattern, $substitutions[$key], $path);
            }
        }

        return $path;
    }
}
