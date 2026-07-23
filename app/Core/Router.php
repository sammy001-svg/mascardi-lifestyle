<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tiny dependency-free front-controller router for the public site.
 * Route patterns support {param} segments, e.g. "/pillars/{slug}".
 */
final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes['GET'][] = [$pattern, $handler];
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->routes['POST'][] = [$pattern, $handler];
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes[$method] ?? [] as [$pattern, $handler]) {
            $params = $this->match($pattern, $path);
            if ($params !== null) {
                // Every state-changing (POST) public route must carry a valid CSRF
                // token. The M-Pesa webhook is intentionally a separate front
                // controller (public/mpesa/callback.php), not routed through here.
                if ($method === 'POST') {
                    Csrf::verifyRequestOrFail();
                }
                $handler(...$params);
                return;
            }
        }

        Response::notFound();
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
