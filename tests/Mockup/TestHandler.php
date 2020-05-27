<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Mockup;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TestHandler
 * @package BorschTest\Mockup
 */
class TestHandler implements RequestHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse(__METHOD__);
    }
}