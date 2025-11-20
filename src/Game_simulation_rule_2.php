<?php

namespace App;

use App\Models\Traits;
use App\Models\Player;
use App\Utils\Deck_factory;
use App\Models\Card;

/**
 * Simuliert das Spiel nach Regelwerk 2:
 * - Die aktuellen Karten kommen nach der Runde weg.
 * - Gewinner erhält einen Punkt.
 * - Die Kategoriewahl wechselt (A, B, A, B...).
 * - Spielende, wenn keine Karten mehr gezogen werden können (Gewinner ist Punktbester).
 */
class Game_simulation_rule_2
{
    private Player $player1;
    private Player $player2;
    private array $scores;

    public function __construct(Player $player1, Player $player2)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->scores = [
            $player1->id => 0,
            $player2->id => 0
        ];
    }

    public static function simulate(): void
    {
        echo "--- SIMULATION START: REGELWERK 2 ---\n\n";

        // Setup: Erstellt 2 Spieler mit je einem halben Deck (10 Karten pro Spieler)
        [$player1, $player2] = Deck_factory::create_test_setup(
            'Spieler A',
            'Spieler B',
            20
        );

        $simulation = new self($player1, $player2);
        $simulation->run_simulation();
    }

    private function run_simulation(): void
    {
        $round = 1;

        // Beide Spieler ziehen ihre Startkarten
        $this->player1->draw_card();
        $this->player2->draw_card();

        echo "Initialer Kartenstand: \n";
        echo "  {$this->player1->name}: {$this->player1->deck->count()} Karten. Aktuelle Karte: " . ($this->player1->current_card ? $this->player1->current_card->name : 'KEINE') . "\n";
        echo "  {$this->player2->name}: {$this->player2->deck->count()} Karten. Aktuelle Karte: " . ($this->player2->current_card ? $this->player2->current_card->name : 'KEINE') . "\n\n";


        // Loop läuft, solange beide Spieler eine aktuelle Karte haben (d.h. ziehen konnten)
        while ($this->player1->current_card !== null && $this->player2->current_card !== null) {
            echo "--- RUNDE {$round} ---\n";

            // Kategorieauswahl: Abwechselnd A und B
            $isPlayerATurn = ($round % 2) !== 0; // Runde 1, 3, 5... -> Spieler A; Runde 2, 4, 6... -> Spieler B
            $currentPlayer = $isPlayerATurn ? $this->player1 : $this->player2;

            $p1Card = $this->player1->current_card;
            $p2Card = $this->player2->current_card;

            // Der aktuelle Spieler wählt seine beste Kategorie
            $selectedTrait = $currentPlayer->current_card->get_best_trait();

            $p1Val = $p1Card->get_val($selectedTrait);
            $p2Val = $p2Card->get_val($selectedTrait);

            echo "Spieler {$currentPlayer->name} (wählt) wählt Kategorie: {$selectedTrait->value} (Wert: {$p1Val} vs {$p2Val})\n";

            $roundWinner = null;

            if ($p1Val > $p2Val) {
                $roundWinner = $this->player1;
            } elseif ($p2Val > $p1Val) {
                $roundWinner = $this->player2;
            }

            // 3. Gewinner bekommt einen Punkt
            if ($roundWinner) {
                $this->scores[$roundWinner->id]++;
                echo "  -> GEWINNER: {$roundWinner->name} bekommt einen Punkt! (Stand: {$this->scores[$this->player1->id]} : {$this->scores[$this->player2->id]})\n";
            } else {
                echo "  Runde UNENTSCHIEDEN! Keine Punkte vergeben. (Stand: {$this->scores[$this->player1->id]} : {$this->scores[$this->player2->id]})\n";
            }

            // 1. und 2. Regel: Die aktuellen Karten kommen weg
            $this->player1->current_card = null;
            $this->player2->current_card = null;

            // Es wird je die nächste Karte gezogen
            $this->player1->draw_card();
            $this->player2->draw_card();

            echo "Kartenstand nach Ziehen:\n";
            echo "  {$this->player1->name}: Deckgröße {$this->player1->deck->count()}. Neue Karte: " . ($this->player1->current_card ? $this->player1->current_card->name : 'KEINE') . "\n";
            echo "  {$this->player2->name}: Deckgröße {$this->player2->deck->count()}. Neue Karte: " . ($this->player2->current_card ? $this->player2->current_card->name : 'KEINE') . "\n\n";

            $round++;
        }

        // --- SPIELENDE ---
        echo "--- SPIEL ENDE ---\n";

        // 4. Game End Condition: Höchster Punktstand gewinnt
        echo "Karten sind aufgebraucht. Endstand:\n";
        echo "  {$this->player1->name} Punkte: {$this->scores[$this->player1->id]}\n";
        echo "  {$this->player2->name} Punkte: {$this->scores[$this->player2->id]}\n";

        $p1Score = $this->scores[$this->player1->id];
        $p2Score = $this->scores[$this->player2->id];

        if ($p1Score > $p2Score) {
            echo "\nGEWINNER ist Spieler {$this->player1->name} mit {$p1Score} Punkten!\n";
        } elseif ($p2Score > $p1Score) {
            echo "\nGEWINNER ist Spieler {$this->player2->name} mit {$p2Score} Punkten!\n";
        } else {
            echo "\nDas Spiel endet UNENTSCHIEDEN!\n";
        }

        echo "Gesamtzahl der Runden: " . ($round - 1) . "\n";
    }
}