<?php

namespace App\Support;

use App\Contracts\SingletonContract;

abstract class Singleton implements SingletonContract
{
    private static $_instances = [];

    final private function __construct()
    {
    }

    public static function getInstance(): SingletonContract
    {
        self::$_instances[static::class] = self::$_instances[static::class] ?? new static();

        return self::$_instances[static::class];
    }

    final private function __clone()
    {
    }

    final private function __wakeup()
    {
    }
}
