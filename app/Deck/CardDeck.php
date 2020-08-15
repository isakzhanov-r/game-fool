<?php


namespace App\Deck;


use App\Support\ArrayService;
use App\Support\Collection;
use App\Support\Singleton;

class CardDeck extends Singleton
{
    /**
     * @var \App\Deck\Card
     */
    public $trump;

    /**
     * @var Collection
     */
    public $cards;

    /**
     * @var Collection
     */
    public $bits = [];

    public function generate(): self
    {
        $cards = [];
        foreach ($this->generateSuits() as $suit) {
            $cards = array_merge($cards, $this->generateCards($suit));
        }
        $this->cards = new Collection($cards);

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

        return $this;
    }

    public function generateSuits()
    {
        return [
            $this->suit('Spades', '♠'),
            $this->suit('Hearts', '♥'),
            $this->suit('Clubs', '♣'),
            $this->suit('Diamonds', '♦'),
        ];
    }

    public function generateCards(CardSuit $suit)
    {
        return [
            $this->card($suit, 'Six', 6),
            $this->card($suit, 'Seven', 7),
            $this->card($suit, 'Eight', 8),
            $this->card($suit, 'Nine', 9),
            $this->card($suit, 'Ten', 10),
            $this->card($suit, 'Jack', 11),
            $this->card($suit, 'Queen', 12),
            $this->card($suit, 'King', 13),
            $this->card($suit, 'Ace', 14),
        ];
    }

    protected function card(CardSuit $suit, $name, $value)
    {
        $uuid = uniqid();

        return new Card(compact('uuid', 'suit', 'name', 'value'));
    }

    protected function suit($name, $icon)
    {
        return new CardSuit(compact('name', 'icon'));
    }
}
