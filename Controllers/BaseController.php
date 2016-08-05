<?php

namespace Controllers;


abstract class BaseController
{
    protected $router;

    public function __construct(\Router $router)
    {
        $this->router = $router;
    }

    protected function getInput() : array
    {
        parse_str(file_get_contents("php://input"), $data);

        return $data;
    }

    protected function response(array $data = [], int $status = 200)
    {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}