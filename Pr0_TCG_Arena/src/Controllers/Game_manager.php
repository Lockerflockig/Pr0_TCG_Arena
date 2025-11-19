<?php

namespace App\Controllers;

class Game_manager
{
    private array $games = [];
    public function register(Game $game): void
    {
        if (!in_array($game, $this->games)) {
            $this->games[] = $game;
            $game
        }
    }

}