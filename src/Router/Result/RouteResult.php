<?php

namespace Borsch\Router\Result;

use Borsch\Router\Contract\RouteInterface;
use Borsch\Router\Contract\RouteResultInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RouteResult
 * @package Borsch\Router
 */
class RouteResult implements RouteResultInterface
{

    /** @var RouteInterface */
    protected RouteInterface $route;

    /** @var array<string, mixed> */
    protected array $params = [];

    /** @var bool */
    protected bool $success;

    /** @var string[]|null */
    protected ?array $methods = null;

    /**
     * RouteResult constructor.
     */
    protected function __construct() {}

    /**
     * @inheritDoc
     */
    public static function fromRouteSuccess(RouteInterface $route, array $params = []): RouteResultInterface
    {
        $result = new self();
        $result->success = true;
        $result->route = $route;
        $result->params = $params;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function fromRouteFailure(array $methods): RouteResultInterface
    {
        $result = new self();
        $result->success = false;
        $result->methods = $methods;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @inheritDoc
     */
    public function getMatchedRoute(): RouteInterface|false
    {
        if ($this->success) {
            return $this->route;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMatchedRouteName(): string|false
    {
        if ($this->success) {
            return $this->route->getName();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMatchedParams(): array
    {
        return $this->params;
    }

    /**
     * @inheritDoc
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * @inheritDoc
     */
    public function isMethodFailure(): bool
    {
        return !$this->success && is_array($this->methods) && count($this->methods);
    }

    /**
     * @inheritDoc
     */
    public function getAllowedMethods(): array
    {
        return $this->methods ?: [];
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        if ($this->success) {
            return $this->route->getHandler()->handle($request);
        }

        return $handler->handle($request);
    }
}
