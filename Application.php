<?php

class Application
{
    protected static $instance;

    protected $router;

    private function __construct(Router $router)
    {
        $this->startAutoloading();
        $this->startErrorHandling();
        $this->router = $router;
    }

    public static function getInstance(Router $router) : self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($router);
        }

        return self::$instance;
    }

    protected function startAutoloading()
    {
        spl_autoload_register(function($class) {
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

            include '..' . DIRECTORY_SEPARATOR . $class . '.php';
        });
    }

    protected function startErrorHandling()
    {
        set_error_handler(function (int $number, string $message, string $file, int $line, array $context) {
            $this->errorHandler($message);
        });

        set_exception_handler(function (Throwable $e) {
            $this->errorHandler($e->getMessage());
        });
    }

    protected function errorHandler(string $message)
    {
        (new \Controllers\DefaultController($this->router))->error($message);
    }

    public function run()
    {
        $route = $this->router->getRouteByUrl($this->router->getUrl());

        $controller = '\Controllers\\' . $route[Router::CONTROLLER];
        $action = $route[Router::ACTION];
        $placeholders = $route[Router::PLACEHOLDERS];

        (new $controller($this->router))->{$action}($placeholders);
    }

}