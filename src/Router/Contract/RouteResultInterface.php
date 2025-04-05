<?php

namespace Borsch\Router\Contract;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface RouteResultInterface
 * @package Borsch\Router
 */
interface RouteResultInterface extends MiddlewareInterface
{

    /**
     * @param RouteInterface $route
     * @param array<string, mixed> $params
     * @return RouteResultInterface
     */
    public static function fromRouteSuccess(RouteInterface $route, array $params = []): RouteResultInterface;

    /**
     * @param string[] $methods
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
    public function getMatchedRoute(): false|RouteInterface;

    /**
     * @return false|string
     */
    public function getMatchedRouteName(): false|string;

    /**
     * @return array<string, mixed>
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
