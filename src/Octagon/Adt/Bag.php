<?php

namespace Octagon\Adt;

/**
 * Bag is a container for key/value pairs.
 */

class Bag
{

    /**
     * Stores key/value pairs.
     *
     * @var array
     */
    private $_collection = array();

    /**
     * Create new Bag instance and add pairs (if any) to bag.
     *
     * @param array $pairs
     *
     * @return void
     */
    public function __construct($pairs = array())
    {
        if (!empty($pairs)) {
            $this->add($pairs);
        }
    }

    /**
     * Return all pairs from the bag.
     *
     * @return array
     */
    public function all()
    {
        return $this->_collection;
    }

    /**
     * Add key-value pairs to bag.
     *
     * @param array $pairs The key-value pairs to add.
     *
     * @return void
     */
    public function add($pairs)
    {
        foreach ($pairs as $key=>$value) {
            $this->_collection[$key] = $value;
        }
    }

    /**
     * Get pair by key.
     *
     * @param string $key
     *
     * @return array|null Returns value if key exists, and `null` if key
     *  does not exist.
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->_collection[$key];
        }
        else {
            return null;
        }
    }

    /**
     * Assign value to key.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool Returns `true` if value was successfully assigned to key.
     *  Otherwise, returns `false`.
     */
    public function set($key,$value)
    {
        if ($this->has($key)) {
            $this->_collection[$key] = $value;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Delete pair by key.
     *
     * @param mixed $key Key for key-value pair to delete.
     *
     * @return bool Returns `true` if key-value pair was successfully deleted.
     *  Otherwise, returns `false`.
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->_collection[$key]);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Check if key exists in collection array.
     *
     * @param mixed $key
     *
     * @return bool Returns`true` if key exists. Otherwise, returns `false`.
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_collection);
    }

    /**
     * Check if collection is empty.
     *
     * @return bool Returns `true` if collection is empty. Otherwise,
     *  returns `false`.
     */
    public function isEmpty()
    {
        if (count($this->_collection) == 0) {
            return true;
        }
        else {
            return false;
        }
    }

}
