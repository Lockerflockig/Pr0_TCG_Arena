<?php

namespace App;

use App\Models\Traits;
use App\Models\Player;
use App\Utils\Deck_factory;
use App\Models\Card;

/**
 * Simuliert das Spiel nach Regelwerk 1:
 * - Gewinner nimmt beide Karten und fügt sie seinem Deck hinzu.
 * - Die Kategoriewahl wechselt zum Gewinner der letzten Runde.
 * - Spielende, wenn ein Spieler keine neue Karte mehr ziehen kann (verliert).
 */
class Game_simulation_rule_1
{
    private Player $player1;
    private Player $player2;

    public function __construct(Player $player1, Player $player2)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
    }

    public static function simulate(): void
    {
        echo "--- SIMULATION START: REGELWERK 1 ---\n\n";

        // Setup: Erstellt 2 Spieler mit je einem halben Deck (10 Karten pro Spieler)
        [$player1, $player2] = Deck_factory::create_test_setup(
            'Spieler A (Starter)',
            'Spieler B',
            20
        );

        $simulation = new self($player1, $player2);
        $simulation->run_simulation();
    }

    private function run_simulation(): void
    {
        $round = 1;
        $currentPlayer = $this->player1; // Spieler A beginnt mit der Kategoriewahl

        // Beide Spieler ziehen ihre Startkarten
        $this->player1->draw_card();
        $this->player2->draw_card();

        echo "Initialer Kartenstand: \n";
        echo "  {$this->player1->name}: {$this->player1->deck->count()} Karten. Aktuelle Karte: " . ($this->player1->current_card ? $this->player1->current_card->name : 'KEINE') . "\n";
        echo "  {$this->player2->name}: {$this->player2->deck->count()} Karten. Aktuelle Karte: " . ($this->player2->current_card ? $this->player2->current_card->name : 'KEINE') . "\n\n";


        while ($this->player1->is_alive() && $this->player2->is_alive()) {
            echo "--- RUNDE {$round} ---\n";

            $p1Card = $this->player1->current_card;
            $p2Card = $this->player2->current_card;

            // Prüfen, ob beide Spieler eine Karte haben, ansonsten ist das Spiel zu Ende (Endbedingung)
            if ($p1Card === null || $p2Card === null) {
                break;
            }

            // Der aktuelle Spieler (Rundengewinner der Vorrunde) wählt die Kategorie.
            $selectedTrait = $currentPlayer->current_card->get_best_trait();

            $p1Val = $p1Card->get_val($selectedTrait);
            $p2Val = $p2Card->get_val($selectedTrait);

            echo "Spieler {$currentPlayer->name} wählt Kategorie: {$selectedTrait->value} (Wert: {$p1Val} vs {$p2Val})\n";

            $roundWinner = null;
            $cardsWon = [$p1Card, $p2Card];

            if ($p1Val > $p2Val) {
                $roundWinner = $this->player1;
            } elseif ($p2Val > $p1Val) {
                $roundWinner = $this->player2;
            } else {
                // Unentschieden: Beide Karten werden verworfen (entsprechend der Interpretation von "einer gewinnt").
                echo "  Runde UNENTSCHIEDEN! Beide Karten werden verworfen.\n";
            }

            // Karten verarbeiten
            $this->player1->current_card = null;
            $this->player2->current_card = null;

            if ($roundWinner) {
                echo "  -> GEWINNER: {$roundWinner->name} gewinnt die Runde und nimmt 2 Karten!\n";
                // Gewinner bekommt beide Karten (seine eigene + die des Gegners)
                foreach ($cardsWon as $card) {
                    $roundWinner->deck->add_card_to_bottom($card);
                }
                // Der Rundengewinner ist der nächste Kategoriwähler
                $currentPlayer = $roundWinner;
            } else {
                // Bei Unentschieden: Der Spieler, der die letzte Kategorie gewählt hat, wählt erneut.
                echo "  -> Der Kategoriwähler ({$currentPlayer->name}) bleibt für die nächste Runde.\n";
            }

            // Neue Karten ziehen
            $this->player1->draw_card();
            $this->player2->draw_card();

            echo "Kartenstand nach Ziehen:\n";
            echo "  {$this->player1->name}: Deckgröße {$this->player1->deck->count()}. Neue Karte: " . ($this->player1->current_card ? $this->player1->current_card->name : 'KEINE') . "\n";
            echo "  {$this->player2->name}: Deckgröße {$this->player2->deck->count()}. Neue Karte: " . ($this->player2->current_card ? $this->player2->current_card->name : 'KEINE') . "\n\n";

            $round++;
        }

        // --- SPIELENDE ---
        echo "--- SPIEL ENDE ---\n";

        $winner = null;
        $loser = null;

        // End Condition: Derjenige, der keine Karte mehr ziehen kann (current_card === null), verliert.
        if ($this->player1->current_card === null && $this->player2->current_card !== null) {
            $winner = $this->player2;
            $loser = $this->player1;
        } elseif ($this->player2->current_card === null && $this->player1->current_card !== null) {
            $winner = $this->player1;
            $loser = $this->player2;
        } elseif ($this->player1->current_card === null && $this->player2->current_card === null) {
            // Unentschieden durch gleichzeitiges Ende (Decks waren gleich groß und beide konnten nicht ziehen)
            echo "UNENTSCHIEDEN durch gleichzeitiges Ende (beide Spieler konnten nicht mehr ziehen).\n";
            return;
        }

        if ($winner && $loser) {
            echo "\nSpieler {$loser->name} konnte keine Karte mehr ziehen und VERLIERT das Spiel.\n";
            echo "GEWINNER ist Spieler {$winner->name}!\n";
        }
        echo "Gesamtzahl der Runden: " . ($round - 1) . "\n";
    }
}