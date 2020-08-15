<?php


namespace App\Players;


use App\Support\Collection;
use App\Support\Entity;

final class Player extends Entity
{
    protected $fields = [
        'name', 'position', 'cards',
    ];

    protected $casts = [
        'name'     => 'string',
        'position' => 'int',
        'cards'    => Collection::class,
    ];
}
