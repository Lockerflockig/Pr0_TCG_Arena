<?php

namespace App\Controllers;

use App\Models\Player;

class Game_manager
{
    private static ?Game_manager $instance = null;
    private array $games = [];
    private array $waiting_players = [];

    private function __construct() {}

    public static function get_instance(): Game_manager
    {
        if (self::$instance === null) {
            self::$instance = new Game_manager();
        }
        return self::$instance;
    }

    /**
     * Erstellt ein neues Spiel mit zwei Spielern
     */
    public function create_game(Player $player1, Player $player2): Game
    {
        $game_id = $this->generate_game_id();
        $game = new Game($game_id, $player1, $player2);
        $this->games[$game_id] = $game;

        return $game;
    }

    /**
     * Fügt einen Spieler zur Warteliste hinzu
     */
    public function add_to_queue(Player $player): ?Game
    {
        // Prüfen ob schon jemand wartet
        if (!empty($this->waiting_players)) {
            $opponent = array_shift($this->waiting_players);
            return $this->create_game($opponent, $player);
        }

        // Ansonsten in Queue setzen
        $this->waiting_players[] = $player;
        return null;
    }

    /**
     * Findet ein Spiel anhand der ID
     */
    public function get_game(string $game_id): ?Game
    {
        return $this->games[$game_id] ?? null;
    }

    /**
     * Findet ein Spiel eines Spielers
     */
    public function find_game_by_player(string $player_id): ?Game
    {
        foreach ($this->games as $game) {
            if ($game->player1->id === $player_id || $game->player2->id === $player_id) {
                return $game;
            }
        }
        return null;
    }

    /**
     * Entfernt beendete Spiele
     */
    public function cleanup_finished_games(): int
    {
        $removed = 0;
        foreach ($this->games as $game_id => $game) {
            if ($game->status === 'finished') {
                unset($this->games[$game_id]);
                $removed++;
            }
        }
        return $removed;
    }

    /**
     * Gibt alle aktiven Spiele zurück
     */
    public function get_active_games(): array
    {
        return array_filter($this->games, fn($game) => $game->status === 'playing');
    }

    /**
     * Gibt Statistiken zurück
     */
    public function get_stats(): array
    {
        return [
            'total_games' => count($this->games),
            'active_games' => count($this->get_active_games()),
            'waiting_players' => count($this->waiting_players),
        ];
    }

    private function generate_game_id(): string
    {
        return 'game_' . uniqid() . '_' . bin2hex(random_bytes(4));
    }
}