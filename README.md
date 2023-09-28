# Borsch Router

[![PHP Composer](https://github.com/borschphp/borsch-router/actions/workflows/php.yml/badge.svg)](https://github.com/borschphp/borsch-router/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/borschphp/router/v)](//packagist.org/packages/borschphp/router)
[![License](https://poser.pugx.org/borschphp/router/license)](//packagist.org/packages/borschphp/router)

A FastRoute router implementation, inspired by the one you can find in the excellent [Mezzio Routing Interfaces](https://docs.mezzio.dev/mezzio/v3/features/router/interface/).  
The router is based on [nikic/fastroute](https://github.com/nikic/FastRoute) request router.  

You need to provide a PSR-7 ServerRequestInterface in order to match the routes.  
A PSR-7 ResponseInterface must be returned by the route handler.

## Installation

Via [composer](https://getcomposer.org/) :

`composer require borschphp/router`

## Usage

```php
require_once __DIR__.'/vendor/autoload.php';

$router = new \Borsch\Router\FastRouteRouter();

$router->addRoute(new \Borsch\Router\Route(
    ['GET'],
    '/articles/{id:\d+}[/{title}]',
    new HomeHandler(), // Instance of RequestHandlerInterface
    'test'
));

$server_request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

$route_result = $router->match(\Laminas\Diactoros\ServerRequestFactory::fromGlobals());

// $route_result is an instance of RouteResultInterface
$response = $route_result->getMatchedRoute()->getHandler()->handle($server_request);

// Send the response back to the client or other...
```

## License

The package is licensed under the MIT license. See [License File](https://github.com/borschphp/borsch-router/blob/master/LICENSE.md) for more information.