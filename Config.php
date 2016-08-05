<?php

class Config
{
    protected static $storage = [];

    public static function __callStatic(string $name, array $arguments)
    {
        if (empty(self::$storage)) {
            self::$storage = require_once 'config.php';
        }

        if (!array_key_exists($name, self::$storage)) {
            throw new Exception('Invalid config param ' . $name . '.');
        }

        return self::$storage[$name];
    }
}