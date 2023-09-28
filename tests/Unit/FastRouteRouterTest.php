<?php

use Borsch\Router\Route;
use Borsch\Router\RouteResultInterface;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequest;

test('getCacheFile()', function() {
    expect($this->router->getCacheFile())->toBeNull();
    $this->router->setCacheFile(__DIR__.'/../cache/routes.cache.php');
    expect($this->router->getCacheFile())->toBe(__DIR__.'/../cache/routes.cache.php');
});

test('isCacheDisabled()', function() {
    expect($this->router->isCacheDisabled())->toBeFalse();
    $this->router->setCacheDisabled(true);
    expect($this->router->isCacheDisabled())->toBeTrue();
});

test('setCacheDisabled()', function() {
    $this->router->setCacheDisabled(true);
    expect($this->router->isCacheDisabled())->toBeTrue();
    
    $this->router->setCacheDisabled(false);
    expect($this->router->isCacheDisabled())->toBeFalse();
});

test('addRoute() throws exception when route name already exists', function() {
    $this->router->addRoute(new Route(
        ['GET'],
        '/articles/{id:\d+}[/{title}]',
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

test('getRoutes()', function() {
    $r1 = new Route(['GET'], '/articles/{id:\d+}', new TestHandler(), 'r1');
    $r2 = new Route(['POST'], '/articles', new TestHandler(), 'r2');
    
    $this->router->addRoute($r1);
    $this->router->addRoute($r2);
    
    expect($this->router->getRoutes())->toBe(['r1' => $r1, 'r2' => $r2]);
});

test('generateUri()', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));

    expect($this->router->generateUri('test', ['id' => 42, 'title' => 'blog-post']))->toBe('/articles/42/blog-post')
        ->and($this->router->generateUri('test', ['id' => 42]))->toBe('/articles/42');
});

test('generateUri() throws exception when missing substitutions', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));
    $this->router->generateUri('test', []);
})->throws(RuntimeException::class);

test('generateUri() throws exception when route name does not exist', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));
    $this->router->generateUri('non-existing-route-name', []);
})->throws(InvalidArgumentException::class);

test('match()', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42/blog-post', 'GET');
    
    $route = $this->router->match($server_request);
    
    expect($route)->toBeInstanceOf(RouteResultInterface::class)
        ->and($route->getMatchedRouteName())->toBe('test');
});

test('match() with matched parameters', function() {
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42/blog-post', 'GET');

    $route = $this->router->match($server_request);

    expect($route->getMatchedParams())->toBe(['id' => '42', 'title' => 'blog-post']);
});

test('setCacheFile()', function() {
    $cache_file = __DIR__.'/../cache/routes.cache.php';
    
    expect($this->router->getCacheFile())->toBeNull();
    $this->router->setCacheFile($cache_file);
    expect($this->router->getCacheFile())->toBe($cache_file);
    
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
    
    $this->router->addRoute(new Route(['GET'], '/articles/{id:\d+}[/{title}]', new TestHandler(), 'test'));
    $server_request = new ServerRequest([], [], 'http://example.com/articles/42/blog-post', 'GET');

    $this->router->match($server_request);

    expect($cache_file)->toBeFile();
});
