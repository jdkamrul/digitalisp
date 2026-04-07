<?php

/**
 * Simple PHP SPA Router
 */
class Router {
    private static array $routes = [];
    private static string $prefix = '';

    public static function get(string $path, $handler, array $middleware = []): void {
        self::add('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, $handler, array $middleware = []): void {
        self::add('POST', $path, $handler, $middleware);
    }

    public static function put(string $path, $handler, array $middleware = []): void {
        self::add('PUT', $path, $handler, $middleware);
    }

    public static function delete(string $path, $handler, array $middleware = []): void {
        self::add('DELETE', $path, $handler, $middleware);
    }

    private static function add(string $method, string $path, $handler, array $middleware): void {
        self::$routes[] = [
            'method'     => $method,
            'path'       => self::$prefix . $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function prefix(string $prefix, callable $callback): void {
        $old = self::$prefix;
        self::$prefix = $old . $prefix;
        $callback();
        self::$prefix = $old;
    }

    public static function dispatch(string $method, string $path): void {
        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['path']);
            if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $middlewareClass = $mw;
                    (new $middlewareClass())->handle();
                }
                // Call handler
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $matches);
                } elseif (is_string($route['handler']) && str_contains($route['handler'], '@')) {
                    [$class, $method_name] = explode('@', $route['handler']);
                    $controller = new $class();
                    call_user_func_array([$controller, $method_name], $matches);
                }
                return;
            }
        }
        // 404
        http_response_code(404);
        require_once BASE_PATH . '/views/errors/404.php';
    }
}
