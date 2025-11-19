<?php

namespace App\Controllers;

use App\Models\Card;
use App\Models\Player;
use App\Models\Traits\Traits;
use Exception;

class Game
{
    public string $id;
    public readonly int $turn_count;
    public Player $player1;
    public Player $player2;
    public string $current_turn;// player1 or 2
    public string $status = 'waiting'; //waiting, playing, finished
    public Card_manager $card_manager;
    public function __construct(string $game_ID,Player $player1, Player $player2)
    {
        $this->id = $game_ID;
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->turn_count = 1;
        $this->card_manager = new Card_Manager();
    }

    /**
     * @throws Exception
     */
    public function play_card(string $player_id, Card $card): void
    {
        if ($this->current_turn !== $player_id) {
            throw new Exception("Not your turn!");
        }
        $this->card_manager->add($card);
        $this->switch_turn();
    }
    public function select_trait(string $player_id,Traits $trait): void
    {
        if ($this->current_turn !== $player_id) {
            throw new Exception("Not your turn!");
        }
        $this->get_player_by_id($player_id)->select_trait($trait);
    }
    private function get_player_by_id(string $player_id): Player
    {
        if ($this->player1->id == $player_id) {
            return $this->player1;
        }
        return $this->player2;
    }
    private function switch_turn(): void
    {
        if (!$this->player1->is_alive) {
            $this->end_game($this->player2);
        } elseif (!$this->player2->is_alive) {
            $this->end_game($this->player1);
        }
        $this->current_turn = $this->current_turn == $this->player1->id
            ? $this->player2->id
            : $this->player1->id;
    }
    public function end_game(Player $player): void
    {
        echo "player $player->name won!\n";
    }
}