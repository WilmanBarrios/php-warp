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
            throw new WarpInvalidDataException();
        }

        if (func_num_args() > 1) {
            $args = func_get_args();
        }

        return $this->space($args);
    }

    function space($data) {
        return tap($this, function ($warp) use ($data) {
            return $this->data = $data;
        });
    }

    function get($key = null, $default = null)
    {
        $array = $this->data;

        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return value($default);
            }
            $array = $array[$segment];
        }

        return $array;
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
        return (new BlackBox($this->data))->array_flatten();
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

        return (new BlackBox($data))->array_flatten();
    }
}

class BlackBox
{
    function __construct($data)
    {
        $this->data = $data;
    }

    function array_flatten()
    {
        $return = [];
        array_walk_recursive($this->data, function ($value, $key) use (&$return) {
            return ! is_null($value) ? $return[$key] = $value : [];
        });

        return $return;
    }
}

class WarpInvalidDataException extends \Exception {}

function tap($value, $callback) {
    $callback($value);
    return $value;
}

function value($value) {
    return $value instanceof Closure ? $value() : $value;
}
