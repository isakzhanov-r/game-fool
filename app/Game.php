<?php


namespace App;


use App\Deck\Card;
use App\Deck\CardDeck;
use App\Players\Player;
use App\Support\Collection;
use App\Support\PrintView;
use App\Support\Singleton;

class Game extends Singleton
{
    /**
     * @var Collection $players
     */
    protected $players;

    protected $deck;

    /**
     * @return mixed
     */
    public function setPlayers(Player ...$players): self
    {
        $this->players = new Collection($players);

        PrintView::printPlayers($this->players);

        return $this;
    }

    public function renderDeck(): self
    {
        $deck = CardDeck::getInstance();

        $this->deck = $deck->generate()
            ->shuffle()
            ->setTrump();

        PrintView::printDeck($this->deck);

        return $this;
    }

    public function handOut()
    {
        while ($this->players->first()->cards->count() !== 6):
            $this->players->each(function (Player $player) {
                $card = $this->deck->cards->last();
                $this->exceptCard($card);
                $player->cards->push($card);
            });
        endwhile;

        $this->sortPlayers();

        PrintView::printPlayers($this->players);
    }

    private function sortPlayers()
    {
        $this->sortCardPlayers();

        $this->players = $this->players->sortBy(function ($player) {
            return $player->cards->where('suit.name', '=', $this->deck->trump->suit->name)->min('value');
        })->values()->each(function ($player, $key) {
            $player->position = $key;
        });
    }

    private function sortCardPlayers()
    {
        $this->players->each(function (Player $player) {
            $player->cards = $player->cards
                ->sortBy('value')
                ->sortBy(function ($card) {
                    return $card->suit->name === $this->deck->trump->suit->name;
                })->values();
        });
    }

    private function exceptCard(Card $card): void
    {
        $index = $this->deck->cards
            ->where('uuid', $card->uuid)
            ->keys()
            ->toArray();

        $this->deck->cards->except($index);
    }
}
