<?php

use Borsch\Router\Result\RouteResult;
use Borsch\Router\Route;
use BorschTest\Mockup\{RequestHandler, TestHandler};
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;

covers(RouteResult::class);

test('fromRouteSuccess()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');
    $result = RouteResult::fromRouteSuccess($route, ['id' => 42, 'title' => 'test']);

    expect($result->getMatchedRoute())->toBe($route);
});

test('fromRouteFailure()', function () {
    $result = RouteResult::fromRouteFailure([]);
    expect($result->getMatchedRoute())->toBeFalse();
});

test('isFailure()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');

    expect(RouteResult::fromRouteFailure([])->isFailure())->toBeTrue()
        ->and(RouteResult::fromRouteSuccess($route)->isFailure())->toBeFalse();
});

test('getMatchedRouteName()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');

    expect(RouteResult::fromRouteFailure([])->getMatchedRouteName())->toBeFalse()
        ->and(RouteResult::fromRouteSuccess($route)->getMatchedRouteName())->toBe('test');
});

test('getAllowedMethods()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');

    expect(RouteResult::fromRouteFailure(['POST', 'PUT'])->getAllowedMethods())->toBe(['POST', 'PUT'])
        ->and(RouteResult::fromRouteSuccess($route)->getAllowedMethods())->toBe([]);
});

test('isMethodFailure()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');

    expect(RouteResult::fromRouteSuccess($route)->isMethodFailure())->toBeFalse()
        ->and(RouteResult::fromRouteFailure([])->isMethodFailure())->toBeFalse()
        ->and(RouteResult::fromRouteFailure(['POST', 'PUT', 'PATCH'])->isMethodFailure())->toBeTrue();
});

test('getMatchedParams()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');
    $params = ['id' => 42, 'title' => 'test'];

    expect(RouteResult::fromRouteFailure([])->getMatchedParams())->toBe([])
        ->and(RouteResult::fromRouteSuccess($route)->getMatchedParams())->toBe([])
        ->and(RouteResult::fromRouteSuccess($route, $params)->getMatchedParams())->toBe($params);
});

test('isSuccess()', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');

    expect(RouteResult::fromRouteSuccess($route)->isSuccess())->toBeTrue()
        ->and(RouteResult::fromRouteFailure([])->isSuccess())->toBeFalse();
});

test('process() for route success', function () {
    $route = new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test');
    $request_handler = new RequestHandler();
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42/blog-post', 'GET');

    $response = RouteResult::fromRouteSuccess($route, ['id' => 42, 'title' => 'test'])
            ->process($server_request, $request_handler);
    
    expect($response)->toBeInstanceOf(ResponseInterface::class)
        ->and($response->getBody()->getContents())->toBe(TestHandler::class.'::handle');
});

test('process() for route failure', function () {
    $request_handler = new RequestHandler();
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42/blog-post', 'GET');

    $response = RouteResult::fromRouteFailure([])->process($server_request, $request_handler);

    expect($response)->toBeInstanceOf(ResponseInterface::class)
        ->and($response->getBody()->getContents())->toBe(RequestHandler::class.'::handle');
});
