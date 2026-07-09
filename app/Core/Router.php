<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $middlewareAliases = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        if (!in_array('csrf', $middleware, true)) {
            array_unshift($middleware, 'csrf');
        }

        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function aliasMiddleware(string $alias, string $className): void
    {
        $this->middlewareAliases[$alias] = $className;
    }

    public function dispatch(Request $request): void
    {
        (new \App\Middleware\SecurityHeadersMiddleware())->handle($request);

        [$route, $routeParams] = $this->matchRoute($request->method(), $request->path());

        if ($route === null) {
            Response::html(View::render(base_path('app/Views/errors/404.php'), ['title' => 'Page Not Found'], null), 404);
            return;
        }

        $request->setRouteParams($routeParams);

        foreach ($route['middleware'] as $middleware) {
            $this->runMiddleware($middleware, $request);
        }

        $handler = $route['handler'];

        if (is_callable($handler)) {
            $handler($request);
            return;
        }

        [$className, $method] = $handler;
        $controller = new $className();
        $controller->{$method}($request);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $normalized = '/' . trim($path, '/');
        $normalized = $normalized === '//' ? '/' : $normalized;

        $routeData = [
            'handler' => $handler,
            'middleware' => $middleware,
        ];

        if (str_contains($normalized, '{') && str_contains($normalized, '}')) {
            $routeData['regex'] = $this->compileRoutePattern($normalized);
        }

        $this->routes[$method][$normalized] = $routeData;
    }

    private function runMiddleware(string $middleware, Request $request): void
    {
        $params = [];
        $name = $middleware;

        if (str_contains($middleware, ':')) {
            [$name, $parameterString] = explode(':', $middleware, 2);
            $params = array_filter(array_map('trim', explode(',', $parameterString)));
        }

        $className = $this->middlewareAliases[$name] ?? $name;
        $instance = new $className();
        $instance->handle($request, $params);
    }

    private function matchRoute(string $method, string $path): array
    {
        $methodRoutes = $this->routes[$method] ?? [];

        if (isset($methodRoutes[$path])) {
            return [$methodRoutes[$path], []];
        }

        foreach ($methodRoutes as $routePath => $route) {
            $regex = $route['regex'] ?? null;
            if (!is_string($regex) || $regex === '') {
                continue;
            }

            if (preg_match($regex, $path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                $params[$key] = $value;
            }

            return [$route, $params];
        }

        return [null, []];
    }

    private function compileRoutePattern(string $path): string
    {
        $pattern = preg_replace_callback(
            '/\{([A-Za-z_][A-Za-z0-9_]*)\}/',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $path
        );

        return '#^' . $pattern . '$#';
    }
}
