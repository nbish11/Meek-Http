<?php

namespace Meek\Http;

use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

class DataCollection implements ArrayAccess, IteratorAggregate, Countable
{
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function remove($key)
    {
        if ($this->exists($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    public function clear()
    {
        $this->data = [];

        return $this;
    }

    public function replace(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function merge(array $data, $hard = false)
    {
        if ($hard) {
            $this->data = array_replace($this->data, $data);
        } else {
            $this->data = array_merge($this->data, $data);
        }

        return $this;
    }

    public function all()
    {
        return $this->data;
    }

    public function keys()
    {
        return array_keys($this->data);
    }

    public function values()
    {
        return array_values($this->data);
    }

    public function map($callback, $userData = null)
    {
        array_walk_recursive($this->data, $callback, $userdata);

        return $this;
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return $this->exists($key);
    }

    public function __unset($key)
    {
        $this->remove($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function count()
    {
        return count($this->data);
    }
}
