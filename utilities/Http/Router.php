<?php

declare(strict_types=1);

namespace App\Utilities\Http;

use App\Constants\Common\ErrorCodes;
use App\Exceptions\Common\AppException;
use App\Exceptions\Common\NotFoundException;

/**
 * Minimal router: `{name}` placeholders, per-route middleware aliases, [Controller::class, 'method'] handlers.
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: array, middleware: string[]}> */
    private array $routes = [];
    private array $groupMiddleware = [];

    public function __construct(private readonly array $middlewareMap)
    {
    }

    public function get(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('PUT', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middleware);
    }

    /** Applies $middleware to every route registered inside $routes. */
    public function group(array $middleware, callable $routes): void
    {
        $previous = $this->groupMiddleware;
        $this->groupMiddleware = [...$previous, ...$middleware];
        $routes($this);
        $this->groupMiddleware = $previous;
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path();
        $allowed = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            $allowed = true;
            if ($route['method'] !== $request->method()) {
                continue;
            }
            $request->setRouteParams(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            $this->runMiddleware($route['middleware'], $request);
            [$class, $method] = $route['handler'];
            (new $class())->$method($request);
            return;
        }

        if ($allowed) {
            throw new AppException(ErrorCodes::METHOD_NOT_ALLOWED, 'Method not allowed.', 405);
        }
        throw new NotFoundException('Page not found.');
    }

    private function add(string $method, string $pattern, array $handler, array $middleware): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[A-Za-z0-9\-]+)', $pattern) . '$#';
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];
    }

    /** Aliases look like "auth" or "role:admin,manager". */
    private function runMiddleware(array $aliases, Request $request): void
    {
        foreach ($aliases as $alias) {
            [$name, $args] = array_pad(explode(':', $alias, 2), 2, '');
            $class = $this->middlewareMap[$name] ?? throw new \LogicException("Unknown middleware: $name");
            (new $class())->handle($request, $args === '' ? [] : explode(',', $args));
        }
    }
}
