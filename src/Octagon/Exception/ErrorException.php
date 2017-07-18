<?php

class ErrorException extends Exception
{
    public function __construct($message, $code, $level, $file, $line)
    {
        parent::__construct($message, $code);
    }
}
