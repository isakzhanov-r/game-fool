<?php


namespace App\Support;


use App\Deck\Card;
use App\Deck\CardDeck;
use App\Players\Player;

final class PrintView
{
    public static function printPlayers(Collection $players)
    {
        $players->each(function (Player $player) {
            self::printPlayer($player);
        });
    }

    public static function printPlayer(Player $player, Player $player_to = null)
    {
        echo "<p style='color: blue'> $player->name";
        self::printCards($player->cards);

        if (!is_null($player_to)) {
            echo " vs $player_to->name";
            self::printCards($player_to->cards);
        }
        echo '</p>';
    }

    public static function putCard(Player $player, Card $card)
    {
        echo '<p><i>' . $player->name . '</i> +';
        self::printCard($card);
        echo '</p>';
    }

    public static function printBits()
    {
        echo '<span> ---- Бита </span>';
    }

    public static function printTake(Player $player)
    {
        echo '<span> ' . $player->name . ' ---- Взял </span>';
    }

    public static function printDeck(CardDeck $deck)
    {
        echo '<p> Deck = ';
        self::printCards($deck->cards);
        echo '</p>';
    }

    public static function printTrump(CardDeck $deck)
    {
        echo '<p style="color: red"> Trump = ';
        echo $deck->trump->name . '-' . $deck->trump->suit->icon;
        echo '</p>';
    }

    public static function printCards(Collection $cards)
    {
        echo "(";
        $cards->each(function (Card $card) {
            self::printCard($card);
        });
        echo ")";
    }

    public static function printCard(Card $card)
    {
        echo $card->name . '<span style="color:' . $card->suit->color . '">' . $card->suit->icon . '</span>; ';
    }

    public static function motionCard($player, $card)
    {
        echo $player->name . '->';
        self::printCard($card);
    }
}
