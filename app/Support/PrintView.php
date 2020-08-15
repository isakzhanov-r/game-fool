<?php


namespace App\Support;


use App\Deck\Card;
use App\Deck\CardDeck;
use App\Players\Player;

abstract class PrintView
{
    public static function printPlayers(Collection $players)
    {
        echo '<br>';
        $players->each(function (Player $player) {
            echo $player->name . ' - ' . $player->position;

            if (!$player->cards->isEmpty()) {
                self::printCards($player->cards);
            }
        });
        echo '<br>';
    }

    public static function printDeck(CardDeck $deck)
    {
        echo '<p style="color: red"> Trump = ';
        echo $deck->trump->name . '-' . $deck->trump->suit->icon;
        echo '</p>';
        echo '<p> Deck = ';
        self::printCards($deck->cards);
        echo '</p>';
    }

    public static function printCards(Collection $cards)
    {
        echo '<p>';
        $cards->each(function (Card $card) {
            echo $card->name . ' - ', $card->suit->icon . '; ';
        });
        echo '</p>';
    }
}
