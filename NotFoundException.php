<?php

class NotFoundException extends Exception
{
    public function __construct(string $message = null)
    {
        $this->message = $message ?? 'Resource not found';
    }
}