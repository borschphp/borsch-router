<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Borsch\Container\Container;
use Borsch\Router\Loader\AttributeRouteLoader;
use Borsch\Router\Route;
use BorschTest\Mockup\RequestHandler;
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

test('cached routes located in file', function () {
    $container = new Container();
    $cache_file = __DIR__.'/../cache/loader.routes.cache.php';

    $loader = new AttributeRouteLoader([__DIR__.'/../Mockup'], $container, $cache_file);
    $loader->load();

    expect($cache_file)->toBeFile();

    $cached_routes = require $cache_file;

    expect($cached_routes)->toBeArray()
        ->and($cached_routes)->toHaveCount(1)
        ->and($cached_routes[0]['path'])->toBe('/mockup/request-handler')
        ->and($cached_routes[0]['methods'])->toBe(['GET'])
        ->and($cached_routes[0]['handler'])->toBe(RequestHandler::class)
        ->and($cached_routes[0]['name'])->toBe('mockup.request.handler');
});
