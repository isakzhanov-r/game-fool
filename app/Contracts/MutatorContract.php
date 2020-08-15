<?php


namespace App\Contracts;


interface MutatorContract
{
    public function hasSetMutator(string $key);

    public function hasGetMutator(string $key);

    public function setMutator(string $key, $value);

    public function getMutator(string $key, $value);

    public function getMutatorName(string $type, string $key): string;
}
