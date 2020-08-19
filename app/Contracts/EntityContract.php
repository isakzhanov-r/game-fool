<?php

namespace App\Contracts;

interface EntityContract
{
    public function get($key);

    public function set($key, $value);

    public function isFillable($key): bool;
}
