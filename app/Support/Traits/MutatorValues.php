<?php

namespace App\Support\Traits;

trait MutatorValues
{
    public function hasSetMutator(string $key): bool
    {
        return method_exists($this, $this->getMutatorName('set', $key));
    }

    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, $this->getMutatorName('get', $key));
    }

    public function setMutator(string $key, $value)
    {
        return $this->{$this->getMutatorName('set', $key)}($value);
    }

    public function getMutator(string $key, $value)
    {
        return $this->{$this->getMutatorName('get', $key)}($value);
    }

    public function getMutatorName(string $type, string $key): string
    {
        return $type.ucwords(str_replace(['-', '_'], ' ', $key)).'Mutator';
    }
}
