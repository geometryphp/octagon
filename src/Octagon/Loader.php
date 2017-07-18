<?php

namespace Octagon;

/**
 * Loader is responsible for loading files.
 */
class Loader
{
    /**
     * Load file
     *
     * @param string $path The file path.
     */
    public static function load($path)
    {
        if (self::isFile($path) && self::exists($path)) {
            self::loadFile($path);
        }
        else {
            // push error
            $response = new \Octagon\Http\FatalErrorResponse(new \Octagon\View(DEFAULT_ERROR_TEMPLATE);
            $response->send();
            \Octagon\Error::trigger('File does not exist: ' . $path);
        }
    }

    /**
     * Require file.
     */
    public static function loadFile($path)
    {
        require_once $path;
    }

    public static function isFile($path)
    {
        if (!is_dir($path)) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function exists($path)
    {
        if (file_exists($path)) {
            return true;
        }
        else {
            return false;
        }
    }

}
