<?php

class ValidationException extends Exception
{
    public function __construct(string $message = null)
    {
        $this->message = $message ?? 'Invalid data passed';
    }
}