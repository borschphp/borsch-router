<?php

use Borsch\Router\Contract\RouteResultInterface;
use Borsch\Router\Exception\InvalidArgumentException;
use Borsch\Router\Route;
use Borsch\Router\SimpleConditionalRouter;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequest;

covers(SimpleConditionalRouter::class);

test('addRoute() throws exception when route name already exists', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles',
        new TestHandler(),
        'test'
    ));

    $this->router->addRoute(new Route(
        ['POST'],
        '/articles',
        new TestHandler(),
        'test'
    ));
})->throws(InvalidArgumentException::class);

test('getRoutes() method', function() {
    $r1 = new Route(['GET'], '/articles', new TestHandler(), 'r1');
    $r2 = new Route(['POST'], '/articles/latest', new TestHandler(), 'r2');

    $this->router->addRoute($r1);
    $this->router->addRoute($r2);

    expect($this->router->getRoutes())->toBe(['r1' => $r1, 'r2' => $r2]);
});

test('generateUri() method', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/latest', new TestHandler(), 'test'));

    expect($this->router->generateUri('test'))->toBe('/articles/latest')
        ->and($this->router->generateUri('test', ['id' => 42]))->toBe('/articles/latest');
});

test('generateUri() throws exception when route name does not exist', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/latest', new TestHandler(), 'test'));
    $this->router->generateUri('non-existing-route-name');
})->throws(InvalidArgumentException::class);

test('match() method', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/latest', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/latest', 'GET');

    /** @var RouteResultInterface $route_result */
    $route_result = $this->router->match($server_request);

    expect($route_result)->toBeInstanceOf(RouteResultInterface::class)
        ->and($route_result->isSuccess())->toBeTrue()
        ->and($route_result->getMatchedRouteName())->toBe('test');
});
