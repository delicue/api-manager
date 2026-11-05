<?php

namespace App;

/**
 * Simple Router class to handle route registration and dispatching
 */
class Router
{
    protected array $routes = [];
    protected array $data = [];

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function register(string $uri, string $method, callable $action, array $data = []): void
    {
        $this->routes[$method][$uri] = $action;
        $this->data[$uri] = $data;
    }

    public function get(string $uri, callable $action, array $data = []): Router
    {
        $this->register($uri, 'GET', $action, $data);
        return $this;
    }

    public function post(string $uri, callable $action, array $data = []): Router
    {
        $this->register($uri, 'POST', $action, $data);
        return $this;
    }

    public function authenticate(): Router
    {
        // user must be logged in to access routes
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        return $this;
    }

    /**
     * 
     * @param mixed $uri The request URI
     * @param mixed $method The HTTP method (GET, POST, etc.)
     */
    public function dispatch($uri, $method): void
    {
        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];
            if (is_callable($action)) {
                call_user_func($action, ...$this->data[$uri]);
            } elseif (is_string($action)) {
                [$controller, $method] = explode('@', $action);

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();
                    if (method_exists($controllerInstance, $method)) {
                        call_user_func([$controllerInstance, $method]);
                    }
                }
            }
        } else {
            not_found();
        }
    }
}
