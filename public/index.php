<?php

use App\Game;
use App\Players\Player;

require __DIR__ . '/../vendor/autoload.php';


$app = Game::getInstance();

$player1 = new Player(['name' => 'Joan']);
$player2 = new Player(['name' => 'Boan']);
$player3 = new Player(['name' => 'Doan']);

$app->setPlayers($player1, $player2, $player3)
    ->renderDeck()
    ->handOut();

dd($app);
