<?php

namespace Controllers;

use Models\Post;

class PostsController extends BaseController
{
    public function all()
    {
        $post = new Post();
        $this->response($post->all());
    }

    public function show(array $params)
    {
        if (!array_key_exists(\Router::PLACEHOLDER . 'id', $params)) {
            throw new \Exception('Required placeholder id does not exists.');
        }

        $id = intval($params[\Router::PLACEHOLDER . 'id']);

        $post = new Post($id);

        $data = $post->toArray();
        $status = 200;

        if ($data == []) {
            $data = ['Resource not found.'];
            $status = 404;
        }

        $this->response($data, $status);
    }

    public function store()
    {
        $post = new Post();

        try {
            $post->insert($this->getInput());
        } catch (\ValidationException $e) {
            $this->response([$e->getMessage()], 400);
            return;
        }

        $this->response();
    }

    public function update(array $params)
    {
        if (!array_key_exists(\Router::PLACEHOLDER . 'id', $params)) {
            throw new \Exception('Required placeholder id does not exists.');
        }

        $id = intval($params[\Router::PLACEHOLDER . 'id']);

        $post = new Post($id);
        
        $requiredFields = [];
        if (strcasecmp($this->router->getRequestVerb(), \Router::PUT) === 0) {
            $requiredFields = $post->fillableFields();
        }

        try {
            $post->update($this->getInput(), $requiredFields);
        } catch (\NotFoundException $e) {
            $this->response([$e->getMessage()], 404);
            return;
        } catch (\ValidationException $e) {
            $this->response([$e->getMessage()], 400);
            return;
        }

        $this->response();
    }

    public function destroy(array $params)
    {
        if (!array_key_exists(\Router::PLACEHOLDER . 'id', $params)) {
            throw new \Exception('Required placeholder id does not exists.');
        }

        $id = intval($params[\Router::PLACEHOLDER . 'id']);

        $post = new Post($id);

        try {
            $post->delete();
        } catch (\NotFoundException $e) {
            $this->response([$e->getMessage()], 404);
            return;
        }

        $this->response();
    }
}