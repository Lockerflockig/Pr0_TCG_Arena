<?php

namespace App\Controllers;

use App\Models\Card;
use App\Models\Player;
use App\Models\Traits;
use Exception;

class Game
{
    public string $id;
    public int $round_count = 0;
    public Player $player1;
    public Player $player2;
    public Player $current_player; // Wer wählt die Kategorie
    public string $status = 'waiting'; // waiting, playing, finished
    public ?Traits $last_trait = null; // Bei Gleichstand: gleiche Kategorie
    private array $draw_pile = []; // Karten bei Gleichstand

    // Game Limits
    private const MAX_ROUNDS = 50;
    private const SUDDEN_DEATH_ROUND = 35;

    public function __construct(string $game_ID, Player $player1, Player $player2)
    {
        $this->id = $game_ID;
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->current_player = $player1;

        // Beide Spieler ziehen ihre erste Karte
        $player1->draw_card();
        $player2->draw_card();
    }

    public function start_game(): void
    {
        $this->status = 'playing';
    }

    /**
     * @throws Exception
     */
    public function select_trait(string $player_id, Traits $trait): void
    {
        if ($this->status !== 'playing') {
            throw new Exception("Game is not in playing state!");
        }

        if ($this->current_player->id !== $player_id) {
            throw new Exception("Not your turn to select trait!");
        }

        // Bei Gleichstand muss die gleiche Kategorie verwendet werden
        if ($this->last_trait !== null && $this->last_trait !== $trait) {
            throw new Exception("After a draw, the same trait must be used!");
        }

        $this->play_round($trait);
    }

    private function play_round(Traits $trait): void
    {
        $card1 = $this->player1->current_card;
        $card2 = $this->player2->current_card;

        // Wenn ein Spieler keine Karte mehr hat
        if ($card1 === null) {
            $this->end_game($this->player2);
            return;
        }
        if ($card2 === null) {
            $this->end_game($this->player1);
            return;
        }

        $value1 = $card1->get_val($trait);
        $value2 = $card2->get_val($trait);

        $this->round_count++;

        /*
        // Rundenlimit erreicht
        if ($this->round_count >= self::MAX_ROUNDS) {
            $this->end_game_by_timeout();
            return;
        }
        */

        // Karten zum Pot hinzufügen (inklusive evtl. Gleichstand-Karten)
        $this->draw_pile[] = $card1;
        $this->draw_pile[] = $card2;

        if ($value1 === $value2) {
            // SUDDEN DEATH: Nach Runde X keine Gleichstände mehr
            if ($this->round_count >= self::SUDDEN_DEATH_ROUND) {
                echo "⚡ SUDDEN DEATH! No draws allowed!\n";
                // Nicht-aktiver Spieler gewinnt
                $winner = $this->current_player === $this->player1 ? $this->player2 : $this->player1;
                $this->handle_win($winner);
                return;
            }

            // GLEICHSTAND
            $this->last_trait = $trait; // Kategorie bleibt bestehen
            // current_player bleibt gleich

            // Beide Spieler ziehen neue Karten
            $this->player1->draw_card();
            $this->player2->draw_card();

            echo "Draw! Both cards stay in the pot. Same trait must be selected.\n";

        } elseif ($value1 > $value2) {
            // SPIELER 1 GEWINNT
            $this->handle_win($this->player1);

        } else {
            // SPIELER 2 GEWINNT
            $this->handle_win($this->player2);
        }
    }

    private function handle_win(Player $winner): void
    {
        // Gewinner bekommt alle Karten aus dem Pot
        foreach ($this->draw_pile as $card) {
            $winner->deck->add_card_to_bottom($card);
        }

        echo "Player {$winner->name} wins the round and gets " . count($this->draw_pile) . " cards!\n";

        // Pot leeren
        $this->draw_pile = [];

        // Gleichstand-Modus beenden
        $this->last_trait = null;

        // Gewinner ist der nächste, der wählt
        $this->current_player = $winner;

        // Beide ziehen neue Karten
        $this->player1->draw_card();
        $this->player2->draw_card();

        // Prüfen ob jemand keine Karten mehr hat
        if (!$this->player1->is_alive()) {
            $this->end_game($this->player2);
        } elseif (!$this->player2->is_alive()) {
            $this->end_game($this->player1);
        }
    }

    private function end_game(Player $winner): void
    {
        $this->status = 'finished';
        echo "Game Over! Player {$winner->name} wins!\n";
        echo "Total rounds played: {$this->round_count}\n";
    }

    private function end_game_by_timeout(): void
    {
        $this->status = 'finished';

        // Gewinner = wer mehr Karten hat
        $p1_total = $this->player1->deck->count() + ($this->player1->current_card ? 1 : 0);
        $p2_total = $this->player2->deck->count() + ($this->player2->current_card ? 1 : 0);

        echo "⏱️  TIME LIMIT REACHED!\n";
        echo "P1 ({$this->player1->name}): {$p1_total} cards\n";
        echo "P2 ({$this->player2->name}): {$p2_total} cards\n";

        if ($p1_total > $p2_total) {
            echo "Player {$this->player1->name} wins by card count!\n";
        } elseif ($p2_total > $p1_total) {
            echo "Player {$this->player2->name} wins by card count!\n";
        } else {
            echo "Game ended in a DRAW!\n";
        }

        echo "Total rounds played: {$this->round_count}\n";
    }

    public function get_game_state(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'round' => $this->round_count,
            'current_player' => $this->current_player->name,
            'current_player_id' => $this->current_player->id,
            'draw_mode' => $this->last_trait !== null,
            'required_trait' => $this->last_trait?->value,
            'draw_pile_size' => count($this->draw_pile),
            'player1' => [
                'name' => $this->player1->name,
                'cards_left' => $this->player1->deck->count(),
                'current_card' => $this->player1->current_card?->get_name(),
            ],
            'player2' => [
                'name' => $this->player2->name,
                'cards_left' => $this->player2->deck->count(),
                'current_card' => $this->player2->current_card?->get_name(),
            ]
        ];
    }
}