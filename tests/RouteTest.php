<?php

namespace BorschTest;

require_once __DIR__.'/../vendor/autoload.php';

use Borsch\Router\Route;
use Borsch\Router\RouteInterface;
use BorschTest\Mockup\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

class RouteTest extends TestCase
{

    /** @var RouteInterface */
    protected $route;

    public function setUp(): void
    {
        $this->route = new Route(
            ['GET'],
            '/articles/{id:\d+}[/{title}]',
            new TestHandler(),
            'test'
        );
    }

    public function testSetName()
    {
        $this->route->setName(__METHOD__);
        $this->assertEquals(__METHOD__, $this->route->getName());
    }

    public function testGetPath()
    {
        $this->assertEquals('/articles/{id:\d+}[/{title}]', $this->route->getPath());
    }

    public function testGetName()
    {
        $this->route->setName(__METHOD__);
        $this->assertEquals(__METHOD__, $this->route->getName());
    }

    public function testGetAllowedMethods()
    {
        $this->assertEquals(['GET'], $this->route->getAllowedMethods());
    }

    public function testAllowsMethod()
    {
        $this->assertTrue($this->route->allowsMethod('GET'));
        $this->assertFalse($this->route->allowsMethod('POST'));
        $this->assertFalse($this->route->allowsMethod('PUT'));
        $this->assertFalse($this->route->allowsMethod('DELETE'));
    }

    public function testGetHandler()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->route->getHandler());
    }
}
