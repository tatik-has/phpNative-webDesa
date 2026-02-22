<?php

class Router
{
    private static array $routes = [];

    public static function get(string $path, callable|array $handler): void
    {
        self::$routes[] = ['method' => 'GET', 'path' => $path, 'handler' => $handler];
    }

    public static function post(string $path, callable|array $handler): void
    {
        self::$routes[] = ['method' => 'POST', 'path' => $path, 'handler' => $handler];
    }

    public static function delete(string $path, callable|array $handler): void
    {
        self::$routes[] = ['method' => 'DELETE', 'path' => $path, 'handler' => $handler];
    }

    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri    = strtok($_SERVER['REQUEST_URI'], '?');

        // Hapus base path /web-pengajuan dari URI
        $basePath = '/web-pengajuan';
        if (str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        if ($requestUri === '' || $requestUri === false) {
            $requestUri = '/';
        }

        // Support _method override untuk DELETE dari form HTML
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            $pattern = self::buildPattern($route['path']);
            if ($route['method'] === $requestMethod && preg_match($pattern, $requestUri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                self::callHandler($route['handler'], array_values($params));
                return;
            }
        }

        http_response_code(404);
        echo "<h1>404 - Halaman tidak ditemukan</h1>";
    }

    private static function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private static function callHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $instance = new $class();
            call_user_func_array([$instance, $method], $params);
        } else {
            call_user_func_array($handler, $params);
        }
    }
}