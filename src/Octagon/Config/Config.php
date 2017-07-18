<?php

namespace Octagon\Config;

/**
 * App config.
 */

class Config
{

    /**
     * Store config properties as array.
     *
     * @var array
     */
    private $_properties = array();

    /**
     * Create new Config instance and load properties from the given config file (if any).
     *
     * @param string Path to config file.
     *
     * @return void
     */
    public function __construct($path = null)
    {
        $this->load($path);
    }

    /**
     * Load configuration file and store the configuration in $properties.
     *
     * @param string Path to config file.
     *
     * @return void
     */
    public function load($path)
    {
        // TODO: (exception needed here...)
        if (!isset($this->_properties) || empty($this->_properties)) {
            $this->_properties = (require($path));
        }
    }

    /**
     * Assign value to given key.
     *
     * @param mixed $key    Key to store value.
     * @param mixed $value  Value to assign.
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->_properties[$key] = $value;
    }

    /**
     * Retrieve a value from the collection by key.
     *
     * @param mixed $key Key for the value to retrieve.
     *
     * @return bool|mixed Returns value if key exists. Otherwise, `false`
     *  is returned.
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->_properties[$key];
        }
        return false;
    }

    /**
     * Check if the given key exists in the collection.
     *
     * @param mixed $key Key to check.
     *
     * @return bool Returns `true` if key exists. Otherwise, `false`
     *  is returned.
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_properties);
    }
}
