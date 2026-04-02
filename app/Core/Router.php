<?php
/**
 * Simple Router Class
 * Handles URL routing to controllers and actions
 */

class Router
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Register a GET route
     */
    public function get(string $path, array|Closure $handler, array $middleware = []): self
    {
        $this->routes['GET'][$path] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, array|Closure $handler, array $middleware = []): self
    {
        $this->routes['POST'][$path] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
        return $this;
    }

    /**
     * Register a route for both GET and POST
     */
    public function match(array $methods, string $path, array|Closure $handler, array $middleware = []): self
    {
        foreach ($methods as $method) {
            $this->routes[strtoupper($method)][$path] = [
                'handler' => $handler,
                'middleware' => $middleware
            ];
        }
        return $this;
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch(string $url, string $method = 'GET'): void
    {
        $method = strtoupper($method);
        $url = '/' . trim($url, '/');
        
        // Handle empty URL
        if ($url === '/') {
            $url = '/';
        }

        // Find matching route
        $route = $this->findRoute($url, $method);
        
        if ($route === null) {
            // Try to find a route with parameters
            $route = $this->findRouteWithParams($url, $method);
        }

        if ($route === null) {
            http_response_code(404);
            $this->render404();
            return;
        }

        // Run middleware
        foreach ($route['middleware'] as $middlewareClass) {
            $middleware = new $middlewareClass();
            $response = $middleware->handle();
            if ($response === false) {
                return;
            }
        }

        // Extract URL parameters
        $params = $this->extractParams($url, $route['pattern'] ?? null);

        // Call handler (either closure or controller action)
        $handler = $route['handler'];

        if ($handler instanceof Closure) {
            call_user_func_array($handler, $params);
        } else {
            // Call controller action
            [$controllerClass, $action] = $handler;

            if (!class_exists($controllerClass)) {
                throw new Exception("Controller not found: {$controllerClass}");
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $action)) {
                throw new Exception("Action not found: {$action} in {$controllerClass}");
            }

            call_user_func_array([$controller, $action], $params);
        }
    }

    /**
     * Find an exact route match
     */
    private function findRoute(string $url, string $method): ?array
    {
        if (isset($this->routes[$method][$url])) {
            return $this->routes[$method][$url];
        }
        return null;
    }

    /**
     * Find a route with parameters (e.g., /report/view?id=1)
     */
    private function findRouteWithParams(string $url, string $method): ?array
    {
        $basePath = parse_url($url, PHP_URL_PATH);
        $queryString = parse_url($url, PHP_URL_QUERY);
        
        // Parse query string into $_GET
        if ($queryString) {
            parse_str($queryString, $getParams);
            $_GET = array_merge($_GET, $getParams);
        }
        
        if (isset($this->routes[$method][$basePath])) {
            $route = $this->routes[$method][$basePath];
            $route['pattern'] = $basePath;
            return $route;
        }

        return null;
    }

    /**
     * Extract parameters from URL
     */
    private function extractParams(string $url, ?string $pattern): array
    {
        // For now, we're using query strings, so return empty array
        // This can be extended for path parameters like /user/{id}
        return [];
    }

    /**
     * Render 404 page
     */
    private function render404(): void
    {
        http_response_code(404);
        require __DIR__ . '/../Views/errors/404.php';
    }
}
