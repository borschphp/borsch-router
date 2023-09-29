<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->
<a name="readme-top"></a>

<!-- PROJECT SHIELDS -->
[![PHP Composer](https://github.com/borschphp/borsch-router/actions/workflows/php.yml/badge.svg)](https://github.com/borschphp/borsch-router/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/borschphp/router/v)](//packagist.org/packages/borschphp/router)
[![License](https://poser.pugx.org/borschphp/router/license)](//packagist.org/packages/borschphp/router)

<!-- PROJECT LOGO -->
<div align="center">
    <h3 align="center">Borsch Router</h3>

  <p align="center">
    An awesome FastRoute router implementation based on nikic/fast-route request router.
    <!--
    <br />
    <a href="https://github.com/othneildrew/Best-README-Template"><strong>Explore the docs »</strong></a>
    -->
  </p>
</div>

<!-- TABLE OF CONTENTS -->
<br />
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->

## About The Project

A FastRoute router implementation, inspired by the one you can find in the excellent [Mezzio Routing Interfaces](https://docs.mezzio.dev/mezzio/v3/features/router/interface/).  
The router is based on [nikic/fastroute](https://github.com/nikic/FastRoute) request router.

You need to provide a PSR-7 ServerRequestInterface in order to match the routes.  
A PSR-7 ResponseInterface must be returned by the route handler.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

### Prerequisites

You need `PHP >= 8.1` to use `Borsch\Router` but the latest stable version of PHP is always recommended.

It also requires an implementation of PSR-7 HTTP Message.  
The Laminas Diactoros Project is used for testing, and in the examples below.

### Installation

Via [composer](https://getcomposer.org/) :

`composer require borschphp/router`

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- USAGE EXAMPLES -->
## Usage

```php
require_once __DIR__.'/vendor/autoload.php';

$router = new \Borsch\Router\FastRouteRouter();

$router->addRoute(new \Borsch\Router\Route(
    ['GET'],
    '/articles/{id:\d+}[/{slug}]',
    new ArticleHandler(), // Instance of RequestHandlerInterface
    'articles.id.title'
));

$server_request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();

// $route_result is an instance of RouteResultInterface
$route_result = $router->match($server_request);

// $route is an instance of RouteInterface (or false if no match)
$route = $route_result->getMatchedRoute();
if (!$route) {
    return new \Laminas\Diactoros\Response('Not Found', 404);
}

// $response is an instance of ResponseInterface
$response = $route->getHandler()->handle($server_request);

// Send the response back to the client or other...
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any
contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also
simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- LICENSE -->
## License

Distributed under the MIT License. See [License File](https://github.com/borschphp/borsch-router/blob/master/LICENSE.md) for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- ACKNOWLEDGMENTS -->

## Acknowledgments

A big thanks to these projects for inspiration or because they're used in this one:

* [nikic/fast-route](https://github.com/nikic/FastRoute)
* [Mezzio Routing Interface](https://docs.mezzio.dev/mezzio/v3/features/router/interface/)
* [PHP Standards Recommendations](https://www.php-fig.org/psr/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>