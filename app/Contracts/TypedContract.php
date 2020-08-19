<?php

namespace App\Contracts;

interface TypedContract
{
    public function isCallable($value);

    public function isInteger(?int $value): int;

    public function isString(?string $value): string;

    public function isBoolean(?bool $value): bool;

    public function isJSON(?string $value): array;

    public function isArray(?array $value): array;

    public function isObject(?object $value): object;

    public function isInstance($key, $value);
}
