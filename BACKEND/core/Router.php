<?php

class Router
{
    private $routes = [];

    public function register($method, $path, $handler, $middlewares = [])
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function resolve($method, $uri)
    {
        foreach ($this->routes as $route) {

     
            if ($route['method'] === $method && $route['path'] === $uri) {

                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    call_user_func($middleware);
                }

                return call_user_func($route['handler']);
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Route not found']);

        

    }
}
