<?php

namespace BorschTest\Mockup;

use Borsch\Router\Attribute\Controller;
use Borsch\Router\Attribute\Route;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Controller]
class RequestHandler implements RequestHandlerInterface
{

    /**
     * @inheritDoc
     */
    #[Route(methods: ['GET'], path: '/mockup/request-handler', name: 'mockup.request.handler')]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse(__METHOD__);
    }
}
