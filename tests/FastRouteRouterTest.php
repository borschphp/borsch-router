<?php

namespace BorschTest;

require_once __DIR__.'/../vendor/autoload.php';

use Borsch\Router\FastRouteRouter;
use Borsch\Router\Route;
use Borsch\Router\RouteResultInterface;
use BorschTest\Mockup\TestHandler;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FastRouteRouterTest extends TestCase
{

    /** @var FastRouteRouter */
    protected $router;

    public function setUp(): void
    {
        $this->router = new FastRouteRouter();
    }

    public function testGetCacheFile()
    {
        $this->assertNull($this->router->getCacheFile());
        $this->router->setCacheFile(__DIR__.'/cache/routes.cache.php');
        $this->assertEquals(__DIR__.'/cache/routes.cache.php', $this->router->getCacheFile());
    }

    public function testIsCacheDisabled()
    {
        $this->assertFalse($this->router->isCacheDisabled());
        $this->router->setCacheDisabled(true);
        $this->assertTrue($this->router->isCacheDisabled());
    }

    public function testSetCacheDisabled()
    {
        $this->router->setCacheDisabled(true);
        $this->assertTrue($this->router->isCacheDisabled());

        $this->router->setCacheDisabled(false);
        $this->assertFalse($this->router->isCacheDisabled());
    }

    public function testAddRoute()
    {
        $this->expectException(InvalidArgumentException::class);

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
    }

    public function testGenerateUri()
    {
        $this->router->addRoute(new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        ));

        $this->assertEquals('/articles/42/blog-post', $this->router->generateUri('test', [
            'id' => 42,
            'title' => 'blog-post'
        ]));

        $this->assertEquals('/articles/42', $this->router->generateUri('test', [
            'id' => 42
        ]));

        $this->expectException(RuntimeException::class);
        $this->router->generateUri('test', []);

        $this->expectException(InvalidArgumentException::class);
        $this->router->generateUri('non-existing-route-name', []);
    }

    public function testMatch()
    {
        $this->router->addRoute(new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        ));

        $server_request = new ServerRequest(
            [],
            [],
            'http://example.com/articles/42/blog-post',
            'GET'
        );

        $route = $this->router->match($server_request);

        $this->assertInstanceOf(RouteResultInterface::class, $route);
        $this->assertEquals('test', $route->getMatchedRouteName());
    }

    public function testMatchWithMatchedParameters()
    {
        $this->router->addRoute(new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        ));

        $server_request = new ServerRequest(
            [],
            [],
            'http://example.com/articles/42/blog-post',
            'GET'
        );

        $route = $this->router->match($server_request);

        $this->assertEquals(
            [
                'id' => 42,
                'title' => 'blog-post'
            ],
            $route->getMatchedParams()
        );
    }

    public function testSetCacheFile()
    {
        $this->assertNull($this->router->getCacheFile());
        $this->router->setCacheFile(__DIR__.'/cache/routes.cache.php');
        $this->assertEquals(__DIR__.'/cache/routes.cache.php', $this->router->getCacheFile());

        if (file_exists(__DIR__.'/cache/routes.cache.php')) {
            unlink(__DIR__.'/cache/routes.cache.php');
        }

        $this->router->addRoute(new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        ));

        $server_request = new ServerRequest(
            [],
            [],
            'http://example.com/articles/42/blog-post',
            'GET'
        );

        $this->router->match($server_request);

        $this->assertFileExists(__DIR__.'/cache/routes.cache.php');
    }
}
