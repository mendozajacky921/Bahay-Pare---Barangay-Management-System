<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, mixed $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(): void
    {
        $request = new Request();
        $method  = $request->method;
        $path    = rtrim($request->path, '/') ?: '/';

        foreach ($this->routes as $route) {
            $params = $this->match($route['method'], $route['path'], $method, $path);

            if ($params === null) {
                continue;
            }

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                $this->runMiddleware($mw, $request);
            }

            // Dispatch handler
            $this->callHandler($route['handler'], $request, $params);
            return;
        }

        // No route matched
        Response::abort(404);
    }

    /**
     * Match route pattern against request. Returns extracted params or null.
     */
    private function match(string $routeMethod, string $routePath, string $method, string $path): ?array
    {
        if ($routeMethod !== $method) {
            return null;
        }

        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $path, $matches)) {
            return null;
        }

        // Keep only named captures
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Run a middleware by name.
     */
    private function runMiddleware(string $name, Request $request): void
    {
        match (true) {
            $name === 'auth'            => (new \Middleware\AuthMiddleware())->handle($request),
            $name === 'guest'           => (new \Middleware\GuestMiddleware())->handle($request),
            $name === 'csrf'            => (new \Middleware\CsrfMiddleware())->handle($request),
            str_starts_with($name, 'role:') => (function () use ($name, $request) {
                $roles = explode(',', substr($name, 5));
                (new \Middleware\RoleMiddleware($roles))->handle($request);
            })(),
            default => null,
        };
    }

    /**
     * Call the route handler (closure or Controller@method string).
     */
    private function callHandler(mixed $handler, Request $request, array $params): void
    {
        if (is_callable($handler)) {
            $handler($request, $params);
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            // Map namespace shorthand
            $class = str_replace('Controllers\\', '', $class);
            $class = match (true) {
                str_starts_with($class, 'Public\\')   => 'Controllers\\Public\\'   . substr($class, 7),
                str_starts_with($class, 'Auth\\')     => 'Controllers\\Auth\\'     . substr($class, 5),
                str_starts_with($class, 'Resident\\') => 'Controllers\\Resident\\' . substr($class, 9),
                str_starts_with($class, 'Staff\\')    => 'Controllers\\Staff\\'    . substr($class, 6),
                default                               => $class,
            };

            if (!class_exists($class)) {
                error_log("Router: Controller class not found: {$class}");
                Response::abort(500);
            }

            $controller = new $class();
            $controller->$method($request, $params);
            return;
        }

        Response::abort(500, 'Invalid route handler.');
    }
}
