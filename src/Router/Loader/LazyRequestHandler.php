<?php

namespace Borsch\Router\Loader;

use Borsch\Router\Exception\RuntimeException;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

readonly class LazyRequestHandler implements RequestHandlerInterface
{

    public function __construct(
        private string $id,
        private ContainerInterface $container
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->container->get($this->id);
        if (!$handler instanceof RequestHandlerInterface) {
            throw RuntimeException::notARequestHandler($this->id);
        }

        return $handler->handle($request);
    }
}
