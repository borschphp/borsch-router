<?php
/**
 * @author debuss-a
 */

namespace Borsch\Router;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class SimpleConditionalRouter
 * A dead simple router that check if request path matches a route path (substitution not supported).
 * @package Borsch\Router
 */
class SimpleConditionalRouter implements RouterInterface
{

    /** @var RouteInterface[] */
    protected array $routes = [];

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
        foreach ($this->routes as $route) {
            if ($request->getUri()->getPath() == $route->getPath()) {
                $allowed_methods = $route->getAllowedMethods();

                return in_array($request->getMethod(), $allowed_methods) ?
                    RouteResult::fromRouteSuccess($route) :
                    RouteResult::fromRouteFailure($allowed_methods);
            }
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