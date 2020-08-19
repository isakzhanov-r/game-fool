<?php


namespace App\Players;


use App\Support\Collection;
use App\Support\Entity;

final class Player extends Entity
{
    protected $fields = [
        'name', 'cards',
    ];

    protected $casts = [
        'name'     => 'string',
        'cards'    => Collection::class,
    ];
}
