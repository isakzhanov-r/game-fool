<?php

namespace App\Support;

use Exception;

class ArrayService
{
    public static function first(array $array, callable $callback = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return null;
            }

            return array_shift($array);
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
    }

    public static function last(array $array, callable $callback = null)
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return null;
            }

            return array_pop($array);
        }

        foreach (array_reverse($array) as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
    }

    public static function random(array $array, int $number = null)
    {
        if (count($array) < $number ?? 1) {
            throw new Exception('You requested '.$number ?? 1 .'items, but there are only '.count($array).' items available.');
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }
        $keys = array_rand($array, $number);

        return array_filter($array, function ($key) use ($keys) {
            return is_array($keys) ? in_array($key, $keys) : $keys == $key;
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function except(array $array, array $indexes)
    {
        if (count($indexes) === 0) {
            return;
        }

        foreach ($indexes as $index) {
            if (static::exists($array, $index)) {
                unset($array[$index]);

                continue;
            }
        }

        return $array;
    }

    public static function exists(array $array, $key)
    {
        return array_key_exists($key, $array);
    }
}
