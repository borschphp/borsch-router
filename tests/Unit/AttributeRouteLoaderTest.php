<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Borsch\Container\Container;
use Borsch\Router\Loader\AttributeRouteLoader;
use Borsch\Router\Route;
use Psr\Http\Server\RequestHandlerInterface;

covers(AttributeRouteLoader::class);

test('load()', function () {
    $container = new Container();

    $loader = new AttributeRouteLoader([__DIR__.'/../Mockup'], $container);
    $loader->load();

    $routes = $loader->getRoutes();
    expect($routes)->toBeArray()
        ->and($routes)->toHaveCount(1)
        ->and($routes[0])->toBeInstanceOf(Route::class)
        ->and($routes[0])->getPath()->toBe('/mockup/request-handler')
        ->and($routes[0])->getAllowedMethods()->toBe(['GET'])
        ->and($routes[0])->getHandler()->toBeInstanceOf(RequestHandlerInterface::class)
        ->and($routes[0])->getName()->toBe('mockup.request.handler');
});
