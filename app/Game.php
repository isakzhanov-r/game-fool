<?php

namespace App;

use App\Deck\Card;
use App\Deck\CardDeck;
use App\Players\Player;
use App\Support\Collection;
use App\Support\PrintView;
use App\Support\Singleton;
use Exception;

class Game extends Singleton
{
    const MAX_PLAYERS = 5;

    const MIN_PLAYERS = 2;

    /**
     * @var Collection $players
     */
    protected $players;

    /**
     * @var CardDeck
     */
    protected $deck;

    /**
     * В данную переменную складываются карты которыми ходят или отбиваются игроки
     *
     * @var Collection $motions
     */
    protected $motions;

    /**
     * @return mixed
     */
    public function setPlayers(Player ...$players): self
    {
        $players = new Collection($players);

        if ($players->count() < self::MIN_PLAYERS || $players->count() > self::MAX_PLAYERS) {
            throw new Exception("max players in game = " . self::MAX_PLAYERS);
        }
        $this->players = $players;

        return $this;
    }

    public function renderDeck(): self
    {
        $deck = CardDeck::getInstance();

        $this->deck = $deck->generate()
            ->shuffle()
            ->setTrump();

        PrintView::printTrump($this->deck);
        PrintView::printDeck($this->deck);

        return $this;
    }

    /**
     * Первая раздача карт игрокам по кругу
     *
     * @return $this
     */
    public function firstHandOut(): self
    {
        if ($this->deck->hasCardsInDeck()) {
            $this->deck->pushTrumpToDeck();
        }
        for ($i = 0; $i < 6; $i++) {
            $this->players->each(function (Player $player) {
                if ($player->cards->count() < 6 && $this->deck->cards->isNotEmpty()) {
                    $card = $this->deck->cards->last();
                    $this->exceptCard($this->deck->cards, $card);
                    $player->cards->push($card);
                }

                return;
            });
        }

        PrintView::printDeck($this->deck);

        $this->sortCardPlayers();

        return $this;
    }

    /**
     * Игрок берет карты из колоды
     *
     * @param  \App\Players\Player  $player
     */
    public function handOut(Player $player)
    {
        if ($this->deck->hasCardsInDeck()) {
            $this->deck->pushTrumpToDeck();
        }
        while ($player->cards->count() < 6 && $this->deck->cards->isNotEmpty()):
            $card = $this->deck->cards->last();
            $this->exceptCard($this->deck->cards, $card);
            $player->cards->push($card);
            PrintView::putCard($player, $card);
        endwhile;

        $this->sortCardPlayers();
    }

    final public function start()
    {
        $from = $this->players->first();
        $to   = $this->players->next();

        return $this->round($from, $to);
    }

    /**
     * Сортировка игроков с определением очередности у кого меньший козырь
     *
     * @return $this
     */
    public function sortPlayers(): self
    {
        $this->sortCardPlayers();

        $players = $this->players->filter(function ($player) {
            return $player->cards->where('suit.name', '=', $this->deck->game_trump->suit->name)->isNotEmpty();
        });
        $player  = $players->sortBy(function ($player) {
            return $player->cards->where('suit.name', '=', $this->deck->game_trump->suit->name)->min('value');
        })->first();

        $index = $this->players->where('name', $player->name)
            ->keys()
            ->toArray();
        $this->players->except($index);
        $this->players->prepend($player);

        PrintView::printPlayers($this->players);

        return $this;
    }

    /**
     * Данный метод вызывает раздачу карт игрокам , обнуляет $motions, в новом раунде новые карты, и проверяет у кого есть карты
     *
     * @param  \App\Players\Player|null  $player_from
     * @param  \App\Players\Player|null  $player_to
     */
    public function round(Player $player_from = null, Player $player_to = null)
    {
        $this->motions = new Collection();

        switch ($count = $this->playersHasCards()->count()) {
            case 0;
                exit('<p>Ничья</p>');

            case 1;
                $this->whoIsFool();
                exit();

            case ($count < $this->players->count()):
                $this->whoIsWinner();
                $this->players = $this->playersHasCards()->values();
                $this->start();
                break;

            default:
                PrintView::printPlayer($player_from, $player_to);
                $this->motion($player_from, $player_to);
        }
    }

    /**
     * Данный метод рекурсивный - это событие (ход) двух игроков
     *
     * @param  \App\Players\Player  $player_from // Игрок который ходит
     * @param  \App\Players\Player  $player_to // Игрок который отбивается
     * @param  array  $values // сюда складываются значения карт в игре, что бы подобрать подобные карты для следующего
     */
    public function motion(Player $player_from, Player $player_to, &$values = [])
    {
        if ($player_to->cards->isNotEmpty()) {
            $card_from = $this->cardToEnter($player_from->cards, $values);

            if (is_null($card_from)) {
                $this->cardsToBits();

                $this->handOut($player_from);
                $this->handOut($player_to);

                return $this->round($player_to, $this->players->next());
            }
            $this->putCard($player_from, $card_from);

            $card_to = $this->cardToFight($player_to->cards, $card_from);

            if (is_null($card_to)) {

                $this->cardsToEnter($player_from->cards, $values, new Collection())
                    ->each(function ($item) use ($player_from) {
                        $this->putCard($player_from, $item);
                    });
                $this->cardsToPlayer($player_to);

                $this->handOut($player_from);
                $this->handOut($player_to);

                return $this->round($this->players->next(), $this->players->next());
            }
            $this->putCard($player_to, $card_to);

            $this->motion($player_from, $player_to, $values);
        }
        $this->cardsToBits();

        $this->handOut($player_from);
        $this->handOut($player_to);

        return $this->round($this->players->next(), $this->players->next());

    }

    /**
     * Метод убирает из карт игрока - карту которой он ходит
     *
     * @param  \App\Players\Player  $player
     * @param  \App\Deck\Card  $card
     */
    private function putCard(Player $player, Card $card)
    {
        $this->exceptCard($player->cards, $card);
        $this->motions->push($card);

        PrintView::motionCard($player, $card);
    }

    /**
     * Возвращает коллекцию игроков у которых есть карты
     *
     * @return \App\Support\Collection
     */
    private function playersHasCards(): Collection
    {
        return $this->players->filter(function (Player $player) {
            return  $player->cards->isNotEmpty();
        });
    }

    /**
     * Возвращает коллекцию игроков у которых нет карт
     *
     * @return \App\Support\Collection
     */
    private function playersHasNotCards(): Collection
    {
        return $this->players->filter(function (Player $player) {
            return $player->cards->isEmpty();
        });
    }

    /**
     * Метод выбирает карту для хода
     *
     * @param  \App\Support\Collection  $cards // Коллекция карт игрока
     * @param  array  $values //массив значений карт которые в игре
     *
     * @return \App\Deck\Card|null
     */
    private function cardToEnter(Collection $cards, &$values): ?Card
    {
        if (count($values) > 0) {
            $cards = $cards->whereIn('value', $values);
        }

        if ($cards->isEmpty()) {
            return null;
        }

        $card = $this->minCard($cards) ?? $this->minCardTrump($cards);
        array_push($values, $card->value);

        return $card;
    }

    /**
     * Метод рекурсивный выбирает карты которое можно подкинуть еще когда отбивающемуся игроку нечем биться
     *
     * @param  \App\Support\Collection  $cards
     * @param  array  $values
     * @param  \App\Support\Collection  $result
     *
     * @return \App\Support\Collection
     */
    private function cardsToEnter(Collection $cards, &$values, Collection $result): Collection
    {
        $card = $this->cardToEnter($cards, $values);

        if (! is_null($card) && $result->where('uuid', '=', $card->uuid)->isEmpty()) {
            $result->push($card);
            $this->cardsToEnter($cards, $values, $result);
        }

        return $result;
    }

    /**
     * Метод выбирает карту которой он может побить карту соперника
     *
     * @param  \App\Support\Collection  $cards
     * @param  \App\Deck\Card  $card
     *
     * @return \App\Deck\Card|null
     */
    private function cardToFight(Collection $cards, Card $card): ?Card
    {
        return $this->minCard($cards, $card) ?? $this->minCardTrump($cards, $card);
    }

    /**
     * Все карты раунда переводятся в биту
     */
    private function cardsToBits(): void
    {
        $this->motions->each(function ($card) {
            $this->deck->bits->push($card);
        });

        PrintView::printBits();
    }

    /**
     * Все карты раунда переходят игроку
     *
     * @param  \App\Players\Player  $player
     */
    private function cardsToPlayer(Player $player): void
    {
        $this->motions->each(function ($card) use ($player) {
            $player->cards->push($card);
        });
        PrintView::printTake($player);
    }

    /**
     * Минимальная карта из колекции карт игрока, если второй передан то вернется минимальная карта котороя выше переданной вторым параметром
     *
     * @param  \App\Support\Collection  $cards
     * @param  \App\Deck\Card|null  $card
     *
     * @return \App\Deck\Card|null
     */
    private function minCard(Collection $cards, Card $card = null): ?Card
    {
        if (is_null($card)) {
            $withoutTrump = $cards
                ->where('suit.name', '!=', $this->deck->game_trump->suit->name);

            return $withoutTrump
                ->where('value', '=', $withoutTrump->min('value'))
                ->first();
        }

        return $cards
            ->where('suit.name', '=', $card->suit->name)
            ->where('value', '>', $card->value)
            ->first();

    }

    /**
     * Минимальная козырная карта
     *
     * @param  \App\Support\Collection  $cards
     * @param  \App\Deck\Card|null  $card
     *
     * @return \App\Deck\Card|null
     */
    private function minCardTrump(Collection $cards, Card $card = null): ?Card
    {
        $trump = $cards
            ->where('suit.name', '=', $this->deck->game_trump->suit->name);
        if (! is_null($card) && $this->deck->game_trump->suit->name === $card->suit->name) {
            return $trump
                ->where('value', '>', $card->value)
                ->first();
        }

        return $trump
            ->where('value', '=', $trump->min('value'))
            ->first();

    }

    /**
     * Сортировка карт у игроков
     */
    private function sortCardPlayers()
    {
        $this->players->each(function (Player $player) {
            $player->cards = $player->cards
                ->sortBy('value')
                ->sortBy(function ($card) {
                    return $card->suit->name === $this->deck->game_trump->suit->name;
                })->values();
        });
    }

    /**
     * Убираем карту из коллекции
     *
     * @param  \App\Support\Collection  $cards
     * @param  \App\Deck\Card  $card
     */
    private function exceptCard(Collection $cards, Card $card): void
    {
        $index = $cards
            ->where('uuid', $card->uuid)
            ->keys()
            ->toArray();

        $cards->except($index);
    }

    private function whoIsFool()
    {
        $fool = $this->playersHasCards()->first();

        echo '<p style="color: red">' . $fool->name . ' - Дурак</p>';

        return $this;
    }

    private function whoIsWinner()
    {
        $winner = $this->playersHasNotCards()->first();

        echo '<p style="color: green">' . $winner->name . ' - Победитель</p>';

        return $this;
    }
}
