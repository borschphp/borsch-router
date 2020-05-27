<?php
/**
 * @author debuss-a
 */

namespace Borsch\Router;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Route
 * @package Borsch\Router
 */
class Route implements RouteInterface
{

    /** @var array */
    protected $methods;

    /** @var string */
    protected $path;

    /** @var RequestHandlerInterface */
    protected $handler;

    /** @var string */
    protected $name;

    /**
     * @inheritDoc
     */
    public function __construct(array $methods, string $path, RequestHandlerInterface $handler, ?string $name = null)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->path = $path;
        $this->handler = $handler;
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