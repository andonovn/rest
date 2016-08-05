<?php

namespace Models;

class Post extends Base
{
    public function table() : string
    {
        return 'posts';
    }

    public function fields() : array
    {
        return ['id', 'title', 'date', 'text'];
    }

    public function primaryKeys() : array
    {
        return ['id'];
    }

    public function requiredFields() : array
    {
        return ['title', 'date', 'text'];
    }

    protected function _validate(array $params)
    {
        if (array_key_exists('id', $params)) {
            $object = new self($params['id']);
            if ($object->toArray() == []) {
                throw new \ValidationException('Invalid id passed.');
            }
        }

        if (array_key_exists('title', $params)) {
            if (mb_strlen($params['title']) > self::MAX_VARCHAR_LENGTH) {
                throw new \ValidationException('Invalid title passed.');
            }
        }

        if (array_key_exists('date', $params)) {
            if (
                !is_numeric($params['date'])
                || $params['date'] - intval($params['date']) !== 0
                || $params['date'] <= 0
            ) {
                throw new \ValidationException('Invalid date passed.');
            }
        }

        if (array_key_exists('text', $params)) {
            if (mb_strlen($params['text']) > self::MAX_TEXT_LENGTH) {
                throw new \ValidationException('Invalid text passed.');
            }
        }
    }
}