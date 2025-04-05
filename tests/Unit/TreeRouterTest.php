<?php

use Borsch\Router\Contract\RouteResultInterface;
use Borsch\Router\Exception\{RuntimeException, InvalidArgumentException};
use Borsch\Router\Route;
use Borsch\Router\TreeRouter;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequest;

covers(TreeRouter::class);

test('addRoute() throws exception when route name already exists', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles/{id:\d+}',
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

test('addRoute() actually add the route', function() {
    $route = new Route(
        ['GET'],
        '/articles/{id:\d+}',
        new TestHandler(),
        'test'
    );
    $this->router->addRoute($route);

    expect($this->router->getRoutes())->toHaveCount(1)
        ->and($this->router->getRoutes())->toHaveKey('test')
        ->and($this->router->getRoutes()['test'])->toBe($route);
});

test('match() returns RouteResultInterface (success)', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles/{id:\d+}',
        new TestHandler(),
        'test'
    ));

    $request = new ServerRequest(uri: '/articles/1', method: 'GET');
    $result = $this->router->match($request);

    expect($result)->toBeInstanceOf(RouteResultInterface::class)
        ->and($result->isSuccess())->toBeTrue();
});

test('match() returns RouteResultInterface (failure)', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles/{id:\d+}',
        new TestHandler(),
        'test'
    ));

    $request = new ServerRequest(uri: '/users/1', method: 'GET');
    $result = $this->router->match($request);

    expect($result)->toBeInstanceOf(RouteResultInterface::class)
        ->and($result->isFailure())->toBeTrue();
});

test('match() returns RouteResultInterface (failure - allowed methods)', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles/{id:\d+}',
        new TestHandler(),
        'test'
    ));

    $request = new ServerRequest(uri: '/articles/1', method: 'POST');
    /** @var RouteResultInterface $result */
    $result = $this->router->match($request);

    expect($result)->toBeInstanceOf(RouteResultInterface::class)
        ->and($result->isFailure())->toBeTrue()
        ->and($result->isMethodFailure())->toBeTrue()
        ->and($result->getAllowedMethods())->toBe(['GET']);
});

test('generateUri()', function() {
    $this->router->addRoute(new Route(['GET'], '/articles', new TestHandler(), 'simple'));
    expect($this->router->generateUri('simple'))->toBe('/articles');

    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'with-id'));
    expect($this->router->generateUri('with-id', ['id' => 42]))->toBe('/articles/42');
});

test('generateUri() throws exception when missing substitutions', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $this->router->generateUri('test', []);
})->throws(RuntimeException::class);

test('generateUri() throws exception when route name does not exist', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $this->router->generateUri('non-existing-route-name', []);
})->throws(InvalidArgumentException::class);

test('generateUri() throws exception when route parts does not match constraint', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $this->router->generateUri('test', ['id' => 'avion']);
})->throws(RuntimeException::class);

test('generateUri() throws exception when route parts is missing', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $this->router->generateUri('test', ['notanid' => 123]);
})->throws(RuntimeException::class);

test('match()', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42', 'GET');

    $route = $this->router->match($server_request);

    expect($route)->toBeInstanceOf(RouteResultInterface::class)
        ->and($route->getMatchedRouteName())->toBe('test');
});

test('match() with matched parameters', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42', 'GET');

    $route = $this->router->match($server_request);

    expect($route->getMatchedParams())->toBe(['id' => '42']);
});

test('match() with route failure Method Not Allowed', function () {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42', 'POST');

    /** @var RouteResultInterface $route */
    $route = $this->router->match($server_request);

    expect($route->isFailure())->toBeTrue()
        ->and($route->isMethodFailure())->toBeTrue()
        ->and($route->getAllowedMethods())->toBeArray()
        ->and($route->getAllowedMethods())->toHaveCount(1)
        ->and($route->getAllowedMethods())->toContain('GET');
});

test('match() with route failure Not Found', function () {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/pokemon/25', 'POST');

    /** @var RouteResultInterface $route */
    $route = $this->router->match($server_request);

    expect($route->isFailure())->toBeTrue()
        ->and($route->isMethodFailure())->toBeFalse()
        ->and($route->getAllowedMethods())->toBeArray()
        ->and($route->getAllowedMethods())->toHaveCount(0)
    ;
});
