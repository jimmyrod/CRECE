<?php

declare(strict_types=1);

namespace App\Core;

use App\Support\Response;
use Closure;
use RuntimeException;

class Router
{
    /** @var array<string, array<int, array{pattern:string,handler:callable}>> */
    private array $routes = [];

    public function add(string $method, string $uri, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method] ??= [];
        $pattern = $this->convertPattern($uri);
        $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function get(string $uri, callable $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, callable $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    public function match(string $method, string $path): mixed
    {
        $method = strtoupper($method);
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $route) {
            $pattern = $route['pattern'];
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return [$route['handler'], $matches];
            }
        }

        return null;
    }

    public function dispatch(string $method, string $path): void
    {
        $match = $this->match($method, $path);
        if ($match === null) {
            Response::view('errors/404.php', ['path' => $path], 404);
            return;
        }

        [$handler, $parameters] = $match;
        $response = ($handler)(...$parameters);
        if ($response instanceof Closure) {
            $response();
        }
    }

    private function convertPattern(string $uri): string
    {
        $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $uri);
        if ($pattern === null) {
            throw new RuntimeException('Unable to compile route pattern.');
        }

        return '#^' . rtrim($pattern, '/') . '/?$#';
    }
}
