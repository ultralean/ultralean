<?php

declare(strict_types=1);

namespace System\Core;

use RuntimeException;

final class Router
{
    private static array $static = [];
    private static array $dynamic = [];
    private static array $named = [];
    private static array $groups = [];
    private static array $routes = [];

    public static function get(string $path, string $action, array $middleware = []): Route
    {
        return self::add('GET', $path, $action, $middleware);
    }

    public static function post(string $path, string $action, array $middleware = []): Route
    {
        return self::add('POST', $path, $action, $middleware);
    }

    public static function put(string $path, string $action, array $middleware = []): Route
    {
        return self::add('PUT', $path, $action, $middleware);
    }

    public static function patch(string $path, string $action, array $middleware = []): Route
    {
        return self::add('PATCH', $path, $action, $middleware);
    }

    public static function delete(string $path, string $action, array $middleware = []): Route
    {
        return self::add('DELETE', $path, $action, $middleware);
    }

    public static function options(string $path, string $action, array $middleware = []): Route
    {
        return self::add('OPTIONS', $path, $action, $middleware);
    }

    public static function any(string $path, string $action, array $middleware = []): Route
    {
        return self::add('ANY', $path, $action, $middleware);
    }

    public static function match(array $methods, string $path, string $action, array $middleware = []): array
    {
        $routes = [];
        foreach ($methods as $method) {
            $routes[] = self::add(strtoupper($method), $path, $action, $middleware);
        }
        return $routes;
    }

    /** Normalize repeated slashes before route matching. */
    public static function cleanPath(string $path): string
    {
        if (str_contains($path, '://')) {
            $path = parse_url($path, PHP_URL_PATH) ?: '/';
        } else {
            $path = explode('?', $path, 2)[0];
            $path = explode('#', $path, 2)[0];
        }

        $path = preg_replace('#/+#', '/', $path) ?: '/';
        $path = '/' . ltrim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function group(string $prefix = '', array $middleware = []): void
    {
        $groupIndex = array_key_last(self::$groups);
        $parent = $groupIndex === null
            ? ['prefix' => '', 'middleware' => []]
            : self::$groups[$groupIndex];

        self::$groups[] = [
            'prefix' => self::joinPaths($parent['prefix'], $prefix),
            'middleware' => array_values(array_unique(array_merge($parent['middleware'], $middleware))),
        ];
    }

    public static function groupEnd(): void
    {
        if (self::$groups === []) {
            throw new RuntimeException('Router::groupEnd() called without an open group.');
        }
        array_pop(self::$groups);
    }

    public static function dispatch(Request $request): Response
    {
        $method = $request->method();
        $originalPath = $request->path();
        $path = self::cleanPath($originalPath);

        // Canonicalize malformed/repeated slashes before route matching.
        // Only redirect safe read requests so POST/PUT/PATCH/DELETE bodies are never lost.
        if (in_array($method, ['GET', 'HEAD'], true) && $path !== $originalPath) {
            $query = $request->queryString();
            $location = $path . ($query !== '' ? '?' . $query : '');
            return Response::redirect($location, 301);
        }

        $route = self::$static[$method][$path] ?? self::$static['ANY'][$path] ?? null;
        $params = [];

        if ($route === null) {
            [$route, $params] = self::matchDynamic($method, $path);
        }

        if ($route === null) {
            $allowed = self::allowedMethods($path);
            if ($allowed !== []) {
                throw new RuntimeException('No route is available for this HTTP method.', 405);
            }
            throw new RuntimeException('The requested route was not found.', 404);
        }

        $handler = self::controllerHandler($route['action'], $params);
        $pipeline = $route['middleware'];
        $next = static fn() => $handler();

        for ($i = count($pipeline) - 1; $i >= 0; $i--) {
            $middleware = self::middlewareInstance($pipeline[$i]);
            $next = static fn() => $middleware->handle($request, $next);
        }

        $response = $next();
        return $response instanceof Response ? $response : Response::make((string) $response);
    }

    public static function url(string $name, array $params = []): string
    {
        if (!isset(self::$named[$name])) {
            throw new RuntimeException("Route [{$name}] not found.");
        }

        $path = self::$named[$name]['path'];
        $path = preg_replace_callback('/\{([A-Za-z_][A-Za-z0-9_]*)(\?)?\}/', static function ($match) use (&$params) {
            $key = $match[1];
            $optional = isset($match[2]);
            if (array_key_exists($key, $params)) {
                return rawurlencode((string) $params[$key]);
            }
            return $optional ? '' : '{' . $key . '}';
        }, $path) ?? $path;

        return self::cleanPath($path);
    }

    public static function setRouteName(int $index, string $name): void
    {
        if (isset(self::$named[$name])) {
            throw new RuntimeException("Duplicate route name: {$name}");
        }

        if (!isset(self::$routes[$index])) {
            throw new RuntimeException('Invalid route index.');
        }

        self::$routes[$index]['name'] = $name;
        self::$named[$name] = self::$routes[$index];
    }

    private static function add(string $method, string $path, string $action, array $middleware): Route
    {
        $groupIndex = array_key_last(self::$groups);
        $group = $groupIndex === null
            ? ['prefix' => '', 'middleware' => []]
            : self::$groups[$groupIndex];

        $path = self::joinPaths($group['prefix'], $path);
        $middleware = array_values(array_unique(array_merge($group['middleware'], $middleware)));

        $route = [
            'method' => strtoupper($method),
            'path' => $path,
            'action' => $action,
            'middleware' => $middleware,
            'name' => null,
        ];

        $index = count(self::$routes);
        self::$routes[] = $route;

        if (self::isStatic($path)) {
            self::$static[$route['method']][$path] = $route;
        } else {
            self::$dynamic[$route['method']][] = self::compileDynamic($route);
        }

        return new Route($index);
    }

    private static function isStatic(string $path): bool
    {
        return !str_contains($path, '{');
    }

    private static function compileDynamic(array $route): array
    {
        $variables = [];
        $segments = explode('/', trim($route['path'], '/'));
        $pattern = '';

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)(\?)?\}$/', $segment, $match) === 1) {
                $variables[] = $match[1];
                $part = '[^/]+';

                if (isset($match[2])) {
                    $pattern .= '(?:/(' . $part . '))?';
                } else {
                    $pattern .= '/(' . $part . ')';
                }
                continue;
            }

            $pattern .= '/' . preg_quote($segment, '#');
        }

        if ($pattern === '') {
            $pattern = '/';
        }

        return $route + [
            'regex' => '#^' . $pattern . '/?$#',
            'variables' => $variables,
        ];
    }

    private static function allowedMethods(string $path): array
    {
        $methods = [];

        foreach (self::$static as $method => $routes) {
            if ($method !== 'ANY' && isset($routes[$path])) {
                $methods[] = $method;
            }
        }

        foreach (self::$dynamic as $method => $routes) {
            if ($method === 'ANY') {
                continue;
            }
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path) === 1) {
                    $methods[] = $method;
                    break;
                }
            }
        }

        $methods = array_values(array_unique($methods));
        sort($methods);
        return $methods;
    }

    private static function matchDynamic(string $method, string $path): array
    {
        $lists = [];
        if (isset(self::$dynamic[$method])) {
            $lists[] = self::$dynamic[$method];
        }
        if (isset(self::$dynamic['ANY'])) {
            $lists[] = self::$dynamic['ANY'];
        }

        foreach ($lists as $routes) {
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path, $matches) !== 1) {
                    continue;
                }

                $params = [];
                foreach ($route['variables'] as $i => $variable) {
                    if (isset($matches[$i + 1])) {
                        $params[$variable] = $matches[$i + 1];
                    }
                }
                return [$route, $params];
            }
        }

        return [null, []];
    }

    private static function controllerHandler(string $action, array $params): callable
    {
        if (!str_contains($action, '@')) {
            throw new RuntimeException("Invalid controller action: {$action}");
        }

        [$controller, $method] = explode('@', $action, 2);
        $class = 'App\\Controllers\\' . ltrim($controller, '\\');

        if (!class_exists($class)) {
            throw new RuntimeException("Controller not found: {$class}");
        }

        $instance = new $class();

        if (!method_exists($instance, $method)) {
            throw new RuntimeException("Controller method not found: {$class}@{$method}");
        }

        return static fn() => $instance->{$method}(...array_values($params));
    }

    private static function middlewareInstance(string $name): object
    {
        $class = str_contains($name, '\\') ? $name : null;

        if ($class === null) {
            $appClass = 'App\\Middleware\\' . $name . 'Middleware';
            $systemClass = 'System\\Middleware\\' . $name . 'Middleware';
            $class = class_exists($appClass) ? $appClass : $systemClass;
        }

        if (!class_exists($class)) {
            throw new RuntimeException("Middleware not found: {$name}");
        }

        return new $class();
    }

    private static function joinPaths(string $first, string $second): string
    {
        return self::cleanPath('/' . trim($first, '/') . '/' . trim($second, '/'));
    }
}
