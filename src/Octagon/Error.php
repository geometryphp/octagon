<?php

namespace Octagon;

/**
 * Error logs errors
 */

class Error
{

    public static function trigger($msg)
    {
        trigger_error($msg, E_USER_ERROR);
    }

}