<?php

namespace App\Deck;

use App\Support\Entity;

class CardSuit extends Entity
{
    protected $fields = [
        'name',
        'icon',
        'color',
    ];
}
