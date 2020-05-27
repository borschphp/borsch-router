<?php

namespace BorschTest;

require_once __DIR__.'/../vendor/autoload.php';

use Borsch\Router\Route;
use Borsch\Router\RouteResult;
use BorschTest\Mockup\RequestHandler;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RouteResultTest extends TestCase
{

    public function testGetMatchedRouteFromRouteSuccess()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $result = RouteResult::fromRouteSuccess($route, ['id' => 42, 'title' => 'test']);

        $this->assertEquals($route, $result->getMatchedRoute());
    }

    public function testGetMatchedRouteFromRouteFailure()
    {
        $result = RouteResult::fromRouteFailure([]);

        $this->assertFalse($result->getMatchedRoute());
    }

    public function testIsFailure()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertTrue(RouteResult::fromRouteFailure([])->isFailure());
        $this->assertFalse(RouteResult::fromRouteSuccess($route)->isFailure());
    }

    public function testGetMatchedRouteName()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertEquals('test', RouteResult::fromRouteSuccess($route)->getMatchedRouteName());
        $this->assertFalse(RouteResult::fromRouteFailure([])->getMatchedRouteName());
    }

    public function testGetAllowedMethods()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertEquals([], RouteResult::fromRouteSuccess($route)->getAllowedMethods());
        $this->assertEquals(['POST', 'PUT'], RouteResult::fromRouteFailure(['POST', 'PUT'])->getAllowedMethods());
    }

    public function testIsMethodFailure()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertFalse(RouteResult::fromRouteSuccess($route)->isMethodFailure());
        $this->assertFalse(RouteResult::fromRouteFailure([])->isMethodFailure());
        $this->assertTrue(RouteResult::fromRouteFailure(['POST', 'PUT', 'PATCH'])->isMethodFailure());
    }

    public function testGetMatchedParams()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertEquals([], RouteResult::fromRouteFailure([])->getMatchedParams());
        $this->assertEquals([], RouteResult::fromRouteSuccess($route)->getMatchedParams());
        $this->assertEquals(
            ['id' => 42, 'title' => 'test'],
            RouteResult::fromRouteSuccess($route, ['id' => 42, 'title' => 'test'])->getMatchedParams()
        );
    }

    public function testIsSuccess()
    {
        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $this->assertFalse(RouteResult::fromRouteFailure([])->isSuccess());
        $this->assertTrue(RouteResult::fromRouteSuccess($route)->isSuccess());
    }

    public function testProcessForRouteSuccess()
    {
        $server_request = new ServerRequest(
            [],
            [],
            'http://example.com/articles/42/blog-post',
            'GET'
        );

        $request_handler = new RequestHandler();

        $route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );

        $response = RouteResult::fromRouteSuccess($route, ['id' => 42, 'title' => 'test'])
            ->process($server_request, $request_handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    public function testProcessForRouteFailure()
    {
        $server_request = new ServerRequest(
            [],
            [],
            'http://example.com/articles/42/blog-post',
            'GET'
        );

        $request_handler = new RequestHandler();

        $response = RouteResult::fromRouteFailure([])->process($server_request, $request_handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(RequestHandler::class.'::handle', $response->getBody()->getContents());
    }
}
