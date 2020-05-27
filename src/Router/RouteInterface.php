<?php
/**
 * @author debuss-a
 */

namespace Borsch\Router;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface RouteInterface
 * @package Borsch\Router
 */
interface RouteInterface
{

    /**
     * @param string[] $methods
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param string|null $name
     */
    public function __construct(array $methods, string $path, RequestHandlerInterface $handler, ?string $name = null);

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $name
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface;

    /**
     * @return array
     */
    public function getAllowedMethods(): array;

    /**
     * @param string $method
     * @return bool
     */
    public function allowsMethod(string $method): bool;
}
