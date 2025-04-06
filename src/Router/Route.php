<?php

namespace Borsch\Router;

use Borsch\Router\Contract\RouteInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Route
 * @package Borsch\Router
 */
class Route implements RouteInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(
        protected array $methods,
        protected string $path,
        protected RequestHandlerInterface $handler,
        protected ?string $name = null
    ) {
        $this->methods = array_map('strtoupper', array_filter($methods, 'is_string'));
        $this->name = $name ?: sprintf(
            '%s^%s',
            implode(':', $this->methods),
            $this->path
        );
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    /**
     * @inheritDoc
     */
    public function getAllowedMethods(): array
    {
        return $this->methods;
    }

    /**
     * @inheritDoc
     */
    public function allowsMethod(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods);
    }
}