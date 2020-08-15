<?php


namespace App\Deck;


use App\Support\Entity;

class Card extends Entity
{
    protected $fields = [
        'uuid', 'suit', 'name', 'value',
    ];

    protected $casts = [
        'suit' => CardSuit::class,
    ];
}
