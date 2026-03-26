<?php

namespace Parachute\Base;

use Parachute\Contracts\View\View;
use Parachute\Http\Request;
use Parachute\Http\Response;

class Router
{
    protected array $routes = [];

    public function __construct()
    {
        $this->routes = [];
        return $this;
    }

    public function addRoute($method, $path, $handler)
    {
        $parsed = $this->parsePath($path);
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $parsed['pattern'],
            'params' => $parsed['params'],
            'handler' => $handler,
        ];
        return $this;
    }

    protected function parsePath($path)
    {
        $args = [];
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) use (&$args) {
            $args[] = $matches[1];
            return '([^/]+)';
        }, rtrim($path, '/'));
        return [
            'pattern' => '#^' . $pattern . '$#',
            'params' => $args
        ];
    }

    public function get($path, $handler)
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function patch($path, $handler)
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    public function delete($path, $handler)
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $path = rtrim($request->path, '/');
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($request->method) && preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches); // Remove the full match
                $params = array_combine($route['params'], $matches);
                ob_start();
                $ret = call_user_func_array($route['handler'], $params);
                $output = ob_get_clean();
                if ($ret instanceof Response) {
                    return $ret;
                } else if ($ret instanceof View) {
                    return new Response($ret->render());
                }
                return new Response($ret ?? $output);
            }
        }
        throw new \Exception('Not Found', 404);
    }
}
