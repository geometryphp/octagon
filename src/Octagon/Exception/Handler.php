<?php

/**
 * See http://alanstorm.com/laravel_error_handler/ for more details
 */

namespace Octagon\Exception;

use Octagon\Exception\ErrorException;

class Handler
{
    public function register()
    {
        $this->registerErrorHandler();
        $this->registerExceptionHandler();
    }

    protected function registerErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
    }

    protected function registerExceptionHandler()
    {
        set_exception_handler(array($this, 'handleUncaughtException'));
    }

    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }
}
