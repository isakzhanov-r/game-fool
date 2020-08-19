<?php

namespace App\Deck;

use App\Support\Collection;
use App\Support\Singleton;

class CardDeck extends Singleton
{
    /**
     * @var \App\Deck\Card
     */
    public $trump;

    public $game_trump;

    /**
     * @var Collection
     */
    public $cards;

    /**
     * @var Collection
     */
    public $bits;

    public function generate(): self
    {
        $cards = [];
        foreach ($this->generateSuits() as $suit) {
            $cards = array_merge($cards, $this->generateCards($suit));
        }
        $this->cards = new Collection($cards);

        $this->bits = new Collection();

        return $this;
    }

    public function shuffle(): self
    {
        for ($i = 0; $i < 1000; $i++) {
            $card = $this->cards->random(1);
            $this->cards->except($card->keys()->toArray());
            $this->cards->prepend($card->first());
        }

        return $this;
    }

    public function setTrump()
    {
        $trumpCard = $this->cards->random(1);

        if ($trumpCard->first()->value === 14) {
            return $this->setTrump();
        }

        $this->cards->except($trumpCard->keys()->toArray());

        $this->trump = $trumpCard->first();

        $this->game_trump = $this->trump;

        return $this;
    }

    public function generateSuits()
    {
        return [
            $this->suit('Spades', '♠', 'black'),
            $this->suit('Hearts', '♥', 'red'),
            $this->suit('Clubs', '♣', 'black'),
            $this->suit('Diamonds', '♦', 'red'),
        ];
    }

    public function generateCards(CardSuit $suit)
    {
        return [
            $this->card($suit, '6', 6),
            $this->card($suit, '7', 7),
            $this->card($suit, '8', 8),
            $this->card($suit, '9', 9),
            $this->card($suit, '10', 10),
            $this->card($suit, 'J', 11),
            $this->card($suit, 'Q', 12),
            $this->card($suit, 'K', 13),
            $this->card($suit, 'T', 14),
        ];
    }

    public function hasCardsInDeck(): bool
    {
        return $this->cards->isEmpty() && ! is_null($this->trump);
    }

    public function pushTrumpToDeck()
    {
        $this->cards->push($this->trump);
        $this->trump = null;
    }

    protected function card(CardSuit $suit, $name, $value)
    {
        $uuid = uniqid();

        return new Card(compact('uuid', 'suit', 'name', 'value'));
    }

    protected function suit($name, $icon, $color)
    {
        return new CardSuit(compact('name', 'icon', 'color'));
    }
}
