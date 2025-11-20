<?php

namespace App;

use App\Controllers\Game;
use App\Controllers\Game_manager;
use App\Models\Traits\Traits;
use App\Utils\Deck_factory;

class Game_demo
{
    public static function run_test_game(): void
    {
        echo "=== PR0 TCG ARENA - TEST GAME ===\n\n";

        // Test-Spieler erstellen
        [$player1, $player2] = Deck_factory::create_test_game();

        echo "Players created:\n";
        echo "- {$player1->name}: {$player1->deck->count()} cards\n";
        echo "- {$player2->name}: {$player2->deck->count()} cards\n\n";

        // Game Manager verwenden
        $manager = Game_manager::get_instance();
        $game = $manager->create_game($player1, $player2);
        $game->start_game();

        echo "Game started! ID: {$game->id}\n\n";

        // Alle Runden spielen bis zum Ende
        while ($game->status === 'playing') {
            $state = $game->get_game_state();

            echo "--- Round {$state['round']} ---\n";
            echo "Current player: {$state['current_player']}\n";
            echo "P1 ({$player1->name}): {$state['player1']['current_card']} - {$state['player1']['cards_left']} cards left\n";
            echo "P2 ({$player2->name}): {$state['player2']['current_card']} - {$state['player2']['cards_left']} cards left\n";

            // Aktiven Spieler bestimmen
            $current_player = $state['current_player_id'] === $player1->id ? $player1 : $player2;

            // Beste Kategorie wählen (außer bei Gleichstand)
            if ($state['required_trait']) {
                // Bei Gleichstand: gleiche Kategorie
                $trait = Traits::from($state['required_trait']);
                echo "Required trait (draw): {$trait->value}\n";
            } else {
                // Spieler wählt seinen besten Trait
                $trait = $current_player->current_card->get_best_trait();
                echo "Selected trait: {$trait->value} (Best for {$current_player->name})\n";
            }

            // Werte anzeigen
            if ($player1->current_card && $player2->current_card) {
                $val1 = $player1->current_card->get_val($trait);
                $val2 = $player2->current_card->get_val($trait);
                echo "Values: {$val1} vs {$val2}\n";
            }

            try {
                $game->select_trait($state['current_player_id'], $trait);
            } catch (\Exception $e) {
                echo "Error: {$e->getMessage()}\n";
                break;
            }

            echo "\n";
        }

        // Finale Stats
        echo "\n=== FINAL STATS ===\n";
        $final_state = $game->get_game_state();
        echo "Status: {$final_state['status']}\n";
        echo "Rounds played: {$final_state['round']}\n";
        echo "P1 cards: {$final_state['player1']['cards_left']}\n";
        echo "P2 cards: {$final_state['player2']['cards_left']}\n";

        // Manager Stats
        echo "\n=== MANAGER STATS ===\n";
        $stats = $manager->get_stats();
        print_r($stats);
    }
}