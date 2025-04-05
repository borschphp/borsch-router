<?php

use Borsch\Router\Route;
use Psr\Http\Server\RequestHandlerInterface;

covers(Route::class);

test('setName() and getName()', function() {
    $this->route->setName('my route');
    expect($this->route->getName())->toBe('my route');
});

test('getPath()', function() {
    expect($this->route->getPath())->toBe('/articles/{id:\d+}[/{title}]');
});

test('getAllowedMethods()', function() {
    expect($this->route->getAllowedMethods())->toBe(['GET']);
});

test('allowsMethod()', function() {
    expect($this->route->allowsMethod('GET'))->toBeTrue()
        ->and($this->route->allowsMethod('POST'))->toBeFalse()
        ->and($this->route->allowsMethod('PUT'))->toBeFalse()
        ->and($this->route->allowsMethod('DELETE'))->toBeFalse();
});

test('getHandler()', function() {
    expect($this->route->getHandler())->toBeInstanceOf(RequestHandlerInterface::class);
});
