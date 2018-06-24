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

        return new USS($args);
    }
}

class USS
{
    function __construct($data)
    {
        $this->data = $data;
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
                return $this->value($default);
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

    public function map($callback = null)
    {
        return array_map($callback, $this->data);
    }

    public function filter($callback = null)
    {
        if ($callback == null) {
            return array_filter($this->data);
        }

        return array_filter($this->data, $callback);
    }

    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}


class WarpInvalidDataException extends \Exception {}
