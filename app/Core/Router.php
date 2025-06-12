<?php

// Router.php

namespace App\Core;

class Router
{
    protected $routes = [];

    public function get($uri, $callback)
    {
        $this->routes["GET"][$uri] = $callback;
    }

    public function post($uri, $callback)
    {
        $this->routes["POST"][$uri] = $callback;
    }

    public function dispatch()
    {
        $uri = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
        $method = $_SERVER["REQUEST_METHOD"];

        if (array_key_exists($method, $this->routes) && array_key_exists($uri, $this->routes[$method])) {
            $callback = $this->routes[$method][$uri];

            if (is_callable($callback)) {
                call_user_func($callback);
            } elseif (is_string($callback)) {
                list($controller, $method) = explode("@", $callback);
                $controller = "App\\Controllers\\" . $controller;
                $controller = new $controller();
                call_user_func_array([$controller, $method], []);
            }
        } else {
            http_response_code(404);
            echo "404 - Página não encontrada";
        }
    }
}


