<?php

namespace App;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Hand;
use App\Models\Player;
use App\Models\Traits;
use App\Utils\Deck_factory;

class Game_demo
{
    private Player $player1;
    private Player $player2;
    private Player $current_selector;
    private int $round = 0;
    private int $player1_score = 0;
    private int $player2_score = 0;

    private const ROUNDS_TO_PLAY = 4; // 4 Runden = 20 Karten (5 pro Hand × 4)

    public static function run_test_game(): void
    {
        echo "=== TCG Arena - Hand-Based Game ===\n\n";

        $game = new self();
        $game->setup_game();
        $game->play_game();
        $game->show_final_results();
    }

    private function setup_game(): void
    {
        // Erstelle 20 Karten Deck
        $full_deck = Deck_factory::create_random_deck(20);
        $full_deck->shuffle();
        $deck1 = Deck_factory::create_random_deck();
        $deck2 = Deck_factory::create_random_deck();

        $this->player1 = new Player(
            id: 'P1-' . uniqid(),
            name: 'unnamed1',
            deck: $deck1
        );

        $this->player2 = new Player(
            id: 'P2-' . uniqid(),
            name: 'Nero',
            deck: $deck2
        );

        // Erstelle Hands für beide Spieler
        $this->player1->hand = new Hand($this->player1->deck, $this->player1);
        $this->player2->hand = new Hand($this->player2->deck, $this->player2);

        // Spieler 1 beginnt
        $this->current_selector = $this->player1;

        echo "Game Setup Complete!\n";
        echo "Player 1: {$this->player1->name} ({$this->player1->deck->count()} cards)\n";
        echo "Player 2: {$this->player2->name} ({$this->player2->deck->count()} cards)\n";
        echo "---\n\n";
    }

    private function play_game(): void
    {
        while ($this->round < self::ROUNDS_TO_PLAY) {
            $this->round++;
            $this->play_round();

            // Wechsel des Selektors
            $this->current_selector = $this->current_selector === $this->player1
                ? $this->player2
                : $this->player1;
        }
    }

    private function play_round(): void
    {
        echo "ROUND {$this->round}\n";


        // Beide Spieler ziehen 5 Karten
        $this->player1->hand->draw_hand();
        $this->player2->hand->draw_hand();

        echo "Both players draw 5 cards.\n\n";

        // Current selector wählt Trait
        $selector_name = $this->current_selector->name;
        $chosen_trait = $this->select_best_trait($this->current_selector);

        echo "→ {$selector_name} selects trait: {$chosen_trait->value}\n\n";

        // Finde stärkste Karten für gewähltes Trait
        $p1_card = $this->player1->hand->get_highest_value_by_trait(
            $this->player1->hand,
            $chosen_trait
        );
        $p2_card = $this->player2->hand->get_highest_value_by_trait(
            $this->player2->hand,
            $chosen_trait
        );

        $p1_value = $p1_card->get_val($chosen_trait);
        $p2_value = $p2_card->get_val($chosen_trait);


        echo "{$this->player1->name}'s Card: {$p1_card->get_name()}\n";
        echo "{$chosen_trait->value}: {$p1_value}\n";
        echo "        VS\n";
        echo "{$this->player2->name}'s Card: {$p2_card->get_name()}\n";
        echo "{$chosen_trait->value}: {$p2_value}\n";

        // Bestimme Gewinner
        if ($p1_value > $p2_value) {
            $this->player1_score++;
            echo "{$this->player1->name} wins this round! ({$p1_value} > {$p2_value})\n";
        } elseif ($p2_value > $p1_value) {
            $this->player2_score++;
            echo " {$this->player2->name} wins this round! ({$p2_value} > {$p1_value})\n";
        } else {
            echo " DRAW! No points awarded.\n";
        }

        echo "\nCurrent Score: {$this->player1->name} {$this->player1_score} - {$this->player2_score} {$this->player2->name}\n";
        echo "Remaining cards: {$this->player1->name} ({$this->player1->deck->count()}) | {$this->player2->name} ({$this->player2->deck->count()})\n";
        echo "\n";
    }

    private function select_best_trait(Player $player): Traits
    {
        // Intelligente Trait-Wahl: Finde das Trait mit dem höchsten Wert in der Hand
        return $player->hand->get_strongest_trait();
    }

    private function show_final_results(): void
    {
        echo "         GAME OVER                    \n";


        echo "Final Score:\n";
        echo "  {$this->player1->name}: {$this->player1_score} points\n";
        echo "  {$this->player2->name}: {$this->player2_score} points\n\n";

        if ($this->player1_score > $this->player2_score) {
            echo " {$this->player1->name} WINS THE GAME! \n";
        } elseif ($this->player2_score > $this->player1_score) {
            echo " {$this->player2->name} WINS THE GAME! \n";
        } else {
            echo " GAME ENDS IN A DRAW! \n";
        }

        echo "\nTotal rounds played: {$this->round}\n";
    }
}