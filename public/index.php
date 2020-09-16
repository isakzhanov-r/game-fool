<?php

use App\Game;
use App\Players\Player;

require __DIR__.'/../vendor/autoload.php';

$app = Game::getInstance();

$player1 = new Player(['name' => 'Джон Тривольта']);
$player2 = new Player(['name' => 'Сильвестэр Сталоном']);
$player3 = new Player(['name' => 'Стивен Ссигой']);
$player4 = new Player(['name' => 'Брюс Вылез']);
$player5 = new Player(['name' => 'Джейсон Степлер']);

$app->setPlayers($player1, $player2, $player3, $player4, $player5)
    ->renderDeck()
    ->firstHandOut()
    ->sortPlayers()
    ->start();
