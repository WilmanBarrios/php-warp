<?php

namespace Warp;

class Warp
{
    static function __callStatic($method, $args)
    {
        return Space::new()->{$method}(...$args);
    }
}

class Space
{
    function __construct()
    {
        $this->data = [];
    }

    static function new(...$args)
    {
        return new self(...$args);
    }

    function data($args = null)
    {
        if (func_num_args() == 0) {
            throw new InvalidDataException();
        }

        if (func_num_args() > 1) {
            $args = func_get_args();
        }

        return $this->space($args);
    }

    function space($data)
    {
        return tap($this, function ($warp) use ($data) {
            return $this->data = $data;
        });
    }

    function get($key = null, $default = null)
    {
        return BlackBox::new($this->data)->get($key, $default);
    }

    function has($key)
    {
        $array = $this->data;

        if (empty($array) || is_null($key)) {
            return false;
        }
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }

        return true;
    }

    function add($key, $value = null)
    {
        if (is_null(get($this->data, $key))) {
            set($this->data, $key, $value);
        }

        return $this->data;
    }

    function count()
    {
        return count($this->data);
    }

    function sum($key = '')
    {
        if ($key == '') {
            return array_sum($this->data);
        }

        return array_reduce($this->data, function ($carry, $item) use ($key) {
            $carry += $item[$key];
            return $carry;
        });
    }

    function map($callback = null)
    {
        return array_map($callback, $this->data);
    }

    function filter($callback = null)
    {
        if ($callback == null) {
            return array_filter($this->data);
        }

        return array_filter($this->data, $callback);
    }

    function flatten()
    {
        return BlackBox::new($this->data)->flatten();
    }

    function pluck($value, $key = null)
    {
        $results = [];
        foreach ($this->data as $item) {
            $itemValue = $this->data_get($item, $value);
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = $this->data_get($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }
        return $results;
    }

    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        foreach (explode('.', $key) as $segment) {
            if (is_array($target)) {
                if (! array_key_exists($segment, $target)) {
                    return value($default);
                }
                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (! isset($target[$segment])) {
                    return value($default);
                }
                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (! isset($target->{$segment})) {
                    return value($default);
                }
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }

    function flatMap($callback)
    {
        $data = $this->map($callback, $this->data);

        return BlackBox::new($data)->flatten();
    }

    function groupBy($key)
    {
        $return = [];
        foreach ($this->data as $val) {
            $return[$val[$key]][] = $val;
        }

        return $return;
    }

    function merge(...$array)
    {
        return array_merge($this->data, ...$array);
    }
}

class BlackBox
{
    function __construct($data)
    {
        $this->data = $data;
    }

    static function new(...$args)
    {
        return new self(...$args);
    }

    function get($key, $default = null)
    {
        return get($this->data, $key, $default);
    }

    function flatten()
    {
        $return = [];
        array_walk_recursive($this->data, function ($value, $key) use (&$return) {
            return ! is_null($value) ? $return[$key] = $value : [];
        });

        return $return;
    }
}

class InvalidDataException extends \Exception {}

function tap($value, $callback) {
    $callback($value);
    return $value;
}

function value($value) {
    return $value instanceof Closure ? $value() : $value;
}

function get($array, $key, $default = null)
{
    if (is_null($key)) return $array;
    if (isset($array[$key])) return $array[$key];
    foreach (explode('.', $key) as $segment)
    {
        if ( ! is_array($array) || ! array_key_exists($segment, $array))
        {
            return value($default);
        }
        $array = $array[$segment];
    }
    return $array;
}

function set(&$array, $key, $value)
{
    if (is_null($key)) return $array = $value;
    $keys = explode('.', $key);
    while (count($keys) > 1)
    {
        $key = array_shift($keys);
        // If the key doesn't exist at this depth, we will just create an empty array
        // to hold the next value, allowing us to create the arrays to hold final
        // values at the correct depth. Then we'll keep digging into the array.
        if ( ! isset($array[$key]) || ! is_array($array[$key]))
        {
            $array[$key] = array();
        }
        $array =& $array[$key];
    }
    $array[array_shift($keys)] = $value;
    return $array;
}
