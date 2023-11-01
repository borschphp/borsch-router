<?php

use Borsch\Router\FastRouteRouter;
use Borsch\Router\Route;
use Borsch\Router\SimpleConditionalRouter;
use BorschTest\Mockup\TestHandler;

uses()
    ->beforeEach(function () {
        $this->route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );
    })
    ->in('Unit/RouteTest.php');

uses()
    ->beforeEach(function () {
        $this->router = new FastRouteRouter();
    })
    ->in('Unit/FastRouteRouterTest.php');

uses()
    ->beforeEach(function () {
        $this->router = new SimpleConditionalRouter();
    })
    ->in('Unit/SimpleConditionalRouterTest.php');
