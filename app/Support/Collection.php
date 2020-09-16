<?php

namespace App\Support;

use App\Contracts\RepositoryContract;
use App\Contracts\TypedContract;
use App\Support\Traits\TypedValue;

class Collection implements RepositoryContract, TypedContract
{
    use TypedValue;

    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function put($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function push(...$values)
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }

    public function prepend(...$values)
    {
        foreach ($values as $value) {
            array_unshift($this->items, $value);
        }

        return $this;
    }

    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function values()
    {
        return new static(array_values($this->items));
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    public function keys()
    {
        return new static(array_keys($this->items));
    }

    public function toArray()
    {
        return $this->map(function ($value) {
            return $value;
        })->all();
    }

    public function all()
    {
        return $this->items;
    }

    public function has(string $key)
    {
        dd(array_key_exists($key, $this->items));
    }

    public function first(callable $callback = null)
    {
        return ArrayService::first($this->items, $callback);
    }

    public function last(callable $callback = null)
    {
        return ArrayService::last($this->items, $callback);
    }

    public function next()
    {
        if ($next = next($this->items)) {
            return $next;
        }
        reset($this->items);

        return current($this->items);
    }

    public function count()
    {
        return count($this->items);
    }

    public function random(int $number = null)
    {
        if (is_null($number)) {
            return ArrayService::random($this->items);
        }

        return new static(ArrayService::random($this->items, $number));
    }

    public function except(array $keys)
    {
        $this->items = ArrayService::except($this->items, $keys);

        return $this->values();
    }

    public function where($key, $operator = null, $value = null)
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    public function whereIn($key, array $values, $strict = false)
    {
        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array($this->data_get($item, $key), $values, $strict);
        });
    }

    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];
        $callback = $this->retrieverValue($callback);

        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options)
            : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    public function min($callback = null)
    {
        $callback = $this->retrieverValue($callback);

        return $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return !is_null($value);
        })->reduce(function ($result, $value) {
            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    public function max($callback = null)
    {
        $callback = $this->retrieverValue($callback);

        return $this->filter(function ($value) {
            return !is_null($value);
        })->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    protected function retrieverValue($value)
    {
        if ($this->isCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return $this->data_get($item, $value);
        };
    }

    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = $this->data_get($item, $key);
            $strings = array_filter([$retrieved, $value], function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });
            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
            }
        };
    }

    protected function data_get($target, $key)
    {
        if (is_null($key)) {
            return $target;
        }
        $keys = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($keys))) {
            if (is_array($target) && ArrayService::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->public[$segment])) {
                $target = $target->{$segment};
            }
        }

        return $target;
    }
}
