<?php


namespace App\Contracts;


interface SingletonContract
{
    public static function getInstance(): SingletonContract;
}
