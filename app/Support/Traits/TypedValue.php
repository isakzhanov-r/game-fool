<?php

namespace App\Support\Traits;

use Exception;
use stdClass;

trait TypedValue
{
    public function isCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    public function isInteger(?int $value): int
    {
        return is_null($value) ? 0 : $value;
    }

    public function isString(?string $value): string
    {
        return is_null($value) ? '' : $value;
    }

    public function isBoolean(?bool $value): bool
    {
        return is_null($value) ? false : $value;
    }

    public function isJSON(?string $value): array
    {
        if (is_string($value) && is_array(json_decode($value, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            return json_decode($value, true);
        }

        return [];
    }

    public function isArray(?array $value): array
    {
        return is_null($value) ? [] : $value;
    }

    public function isObject(?object $value): object
    {
        return is_null($value) ? new stdClass() : $value;
    }

    public function isInstance($key, $value)
    {
        if ($value instanceof $key) {
            return $value;
        }

        throw new Exception('Invalid Argument');
    }

}
