<?php

class Router
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
    const ALL = 'all';
    const SHOW = 'show';
    const STORE = 'store';
    const UPDATE = 'update';
    const DESTROY = 'destroy';
    const CONTROLLER = 'controller';
    const ACTION = 'action';
    const PLACEHOLDER = ':';
    const PLACEHOLDERS = 'placeholders';
    const CONTROLLER_SUFFIX = 'Controller';

    protected $routes = [
        self::GET => [],
        self::POST => [],
        self::PUT => [],
        self::PATCH => [],
        self::DELETE => [],
    ];

    protected $allowedVerbs = [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE];

    protected function addRoute(string $verb, string $url, string $controller, string $action)
    {
        if (!in_array($verb, $this->allowedVerbs, true)) {
            throw new \Exception('Unknown HTTP verb.');
        }

        $url = $this->trim($url);

        if (isset($this->routes[$verb])) {
            if (array_key_exists($url, $this->routes[$verb])) {
                return;
            }
        }

        $this->routes[$verb][$url][self::CONTROLLER] = $controller;
        $this->routes[$verb][$url][self::ACTION] = $action;
    }

    protected function trim($url)
    {
        return trim($url, "/ \t\n\r\0\x0B");
    }

    public function resource(string $name)
    {
        $controller = ucfirst(strtolower($name)) . self::CONTROLLER_SUFFIX;

        $this->get($name, $controller, self::ALL);
        $this->get($name . '/' . self::PLACEHOLDER . 'id', $controller, self::SHOW);
        $this->post($name, $controller, self::STORE);
        $this->put($name . '/' . self::PLACEHOLDER . 'id', $controller, self::UPDATE);
        $this->patch($name . '/' . self::PLACEHOLDER . 'id', $controller, self::UPDATE);
        $this->delete($name . '/' . self::PLACEHOLDER . 'id', $controller, self::DESTROY);
    }

    public function get(string $url, string $controller, string $action)
    {
        $this->addRoute(self::GET, $url, $controller, $action);
    }

    public function post(string $url, string $controller, string $action)
    {
        $this->addRoute(self::POST, $url, $controller, $action);
    }

    public function put(string $url, string $controller, string $action)
    {
        $this->addRoute(self::PUT, $url, $controller, $action);
    }

    public function patch(string $url, string $controller, string $action)
    {
        $this->addRoute(self::PATCH, $url, $controller, $action);
    }

    public function delete(string $url, string $controller, string $action)
    {
        $this->addRoute(self::DELETE, $url, $controller, $action);
    }

    public function getUrl() : string
    {
        return substr($_SERVER['REQUEST_URI'], strlen(Config::home_url()));
    }

    public function getRouteByUrl(string $url) : array
    {
        $verb = strtolower($this->getRequestVerb());

        if (!array_key_exists($verb, $this->routes)) {
            throw new \Exception('Unknown verb.');
        }

        $urlArray = explode('/', $url);

        foreach ($this->routes[$verb] as $route => $routeDetails) {
            $routeArray = explode('/', $route);

            if (count($routeArray) != count($urlArray)) {
                continue;
            }

            $match = true;
            $placeholders = [];
            foreach ($routeArray as $index => $element) {
                if (strpos($element, self::PLACEHOLDER) === 0) {
                    $placeholders[$element] = $urlArray[$index];
                    continue;
                }

                if (strcmp($routeArray[$index], $urlArray[$index]) !== 0) {
                    $match = false;
                    break;
                }
            }

            if ($match == true) {
                $placeholders = [self::PLACEHOLDERS => $placeholders];
                return array_merge($routeDetails, $placeholders);
            }
        }

        return $this->getDefaultRoute();
    }

    protected function getDefaultRoute()
    {
        return [
            self::CONTROLLER => 'DefaultController',
            self::ACTION => 'notFound',
            self::PLACEHOLDERS => [],
        ];
    }

    public function getRequestVerb()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}