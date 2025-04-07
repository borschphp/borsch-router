<?php

namespace Borsch\Router;

use Borsch\Router\Contract\RouteInterface;
use Borsch\Router\Contract\RouteResultInterface;
use Borsch\Router\Contract\RouterInterface;
use Borsch\Router\Exception\InvalidArgumentException;
use Borsch\Router\Result\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SimpleConditionalRouter
 * A dead simple router that check if request path matches a route path (substitution not supported).
 * @package Borsch\Router
 */
class SimpleConditionalRouter implements RouterInterface
{

    /** @var array<string, RouteInterface> */
    protected array $routes = [];

    /** @var array<string, RouteInterface> */
    protected array $routes_by_path = [];

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function addRoute(RouteInterface $route): void
    {
        if (isset($this->routes[$route->getName()])) {
            throw InvalidArgumentException::routeNameAlreadyExists($route->getName());
        }

        $this->routes[$route->getName()] = $route;
        $this->routes_by_path[$route->getPath()] = $route;
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
        if (isset($this->routes_by_path[$request->getUri()->getPath()])) {
            $route = $this->routes_by_path[$request->getUri()->getPath()];
            $allowed_methods = $route->getAllowedMethods();

            return in_array($request->getMethod(), $allowed_methods) ?
                RouteResult::fromRouteSuccess($route) :
                RouteResult::fromRouteFailure($allowed_methods);
        }

        return RouteResult::fromRouteFailure([]);
    }

    /**
     * @inheritDoc
     */
    public function generateUri(string $name, array $substitutions = []): string
    {
        if (!isset($this->routes[$name])) {
            throw new InvalidArgumentException(sprintf(
                'The route named %s is unknown...',
                $name
            ));
        }

        return $this->routes[$name]->getPath();
    }
}