<?php
/**
 * @author debuss-a
 */

namespace Borsch\Router;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface RouteResultInterface
 * @package Borsch\Router
 */
interface RouteResultInterface extends MiddlewareInterface
{

    /**
     * @param RouteInterface $route
     * @param array $params
     * @return RouteResultInterface
     */
    public static function fromRouteSuccess(RouteInterface $route, array $params = []): RouteResultInterface;

    /**
     * @param array $methods
     * @return RouteResultInterface
     */
    public static function fromRouteFailure(array $methods): RouteResultInterface;

    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return false|RouteInterface
     */
    public function getMatchedRoute();

    /**
     * @return false|string
     */
    public function getMatchedRouteName();

    /**
     * @return array
     */
    public function getMatchedParams(): array;

    /**
     * @return bool
     */
    public function isFailure(): bool;

    /**
     * @return bool
     */
    public function isMethodFailure(): bool;

    /**
     * @return string[]
     */
    public function getAllowedMethods(): array;
}
