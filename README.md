# Transitive\Routing

Routing layer for the Transitive MVP stack.

This package maps request patterns to `Route` objects. A route can point to presenter and view instances, or to presenter and view PHP files that are included at runtime.

[![Latest Stable Version](https://poser.pugx.org/transitive/routing/v/stable?format=flat-square)](https://packagist.org/packages/transitive/routing)
[![License](https://poser.pugx.org/transitive/routing/license?format=flat-square)](https://packagist.org/packages/transitive/routing)

## What is included
- `Transitive\Routing\Route`: executes a presenter/view pair and transfers presenter data into the view.
- `Transitive\Routing\PathRouter`: resolves requests to files under presenter and view directories.
- `Transitive\Routing\ListRouter`: exact-match router backed by an in-memory route map.
- `Transitive\Routing\ListRegexRouter`: regex-based router with match capture support.
- `Transitive\Routing\FrontController`: interface implemented by front controller packages such as `transitive/core` and `transitive/web`.
- `Transitive\Routing\RoutingException`: domain exception for missing routes and runtime routing failures.

## Installation
```sh
composer require transitive/routing
```

PHP `8.1+` is required.

## Basic usage
```php
<?php

use Transitive\Core\Presenter;
use Transitive\Routing\ListRouter;
use Transitive\Routing\Route;
use Transitive\Simple\View;

$route = new Route(new Presenter(), new View());
$route->presenter->addData('message', 'Hello from a route');
$route->view->addContent(function (array $data) {
	return $data['message'];
});

$router = new ListRouter();
$router->addRoute('home', $route);

$matched = $router->execute('home');
$matched?->execute();

echo $matched?->getContent()?->asString() ?? '';
```

## File-based routing
`PathRouter` is the bridge between URL-like patterns and filesystem-backed presenters/views:

```php
<?php

use Transitive\Routing\PathRouter;

$router = new PathRouter(
	__DIR__.'/presenters',
	__DIR__.'/views'
);

$route = $router->execute('blog/post');
```

With this setup, `blog/post` resolves to:
- `presenters/blog/post.php`
- `views/blog/post.php`

The router normalises path traversal segments before resolving the files.

## License

[MIT](LICENSE)
