<?php

namespace Controllers;


class DefaultController extends BaseController
{
    public function notFound()
    {
        $this->response(['Invalid URL.'], 404);
    }

    public function error(string $message = null)
    {
        $error = 'Something went wrong.';

        if (!is_null($message)) {
            $error .= ' ' . $message;
        }

        $this->response([$error], 500);
    }
}