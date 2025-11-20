<?php

namespace App\Utils;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Player;

class Deck_factory
{
    /**
     * Erstellt ein Test-Deck mit zufälligen Karten
     */
    public static function create_random_deck(int $size = 15): Deck
    {
        $cards = [];
        for ($i = 0; $i < $size; $i++) {
            $cards[] = self::create_random_card($i);
        }
        return new Deck($cards);
    }

    /**
     * Erstellt eine zufällige Karte
     */
    private static function create_random_card(int $id): Card
    {
        $names = [
            'bob', 'unnamed', 'marvin',
            'reflexi', 'bonzaiboy', 'rolf',
            '2sp1d', 'ganroc', 'reflexi',
            'timo', 'greathfullD', 'excelnerd',
            'mrgl', 'cumalot', 'hyman',
            'matze', 'messias', 'ballermann',
            'coinflip', 'offz', 'nero',
            'caly', 'unnamed', 'schealla',
            'cheebus', 'pfeffi', 'lausor',
            'banane', 'aalmann', 'Deleted-User'
        ];

        return new Card(
            name: $names[$id % count($names)] . " #$id",
            id: $id,
            rep: random_int(60, 99),
            dip: random_int(60, 99),
            agg: random_int(60, 99),
            end: random_int(60, 99),
            tac: random_int(60, 99),
            act: random_int(60, 99)
        );
    }

    /**
     * Erstellt ein balanciertes Deck (gleiche Kartenstärke)
     */
    public static function create_balanced_deck(int $size = 20): Deck
    {
        $cards = [];
        $avg_power = 5;

        for ($i = 0; $i < $size; $i++) {
            $cards[] = new Card(
                name: "Balanced Card #$i",
                id: $i,
                rep: $avg_power + random_int(-2, 2),
                dip: $avg_power + random_int(-2, 2),
                agg: $avg_power + random_int(-2, 2),
                end: $avg_power + random_int(-2, 2),
                tac: $avg_power + random_int(-2, 2),
                act: $avg_power + random_int(-2, 2)
            );
        }

        return new Deck($cards);
    }

    /**
     * Teilt ein Deck in zwei gleiche Hälften
     */
    public static function split_deck(Deck $deck): array
    {
        $all_cards = $deck->get_all_cards();
        $mid = (int)(count($all_cards) / 2);

        $deck1_cards = array_slice($all_cards, 0, $mid);
        $deck2_cards = array_slice($all_cards, $mid);

        return [
            new Deck($deck1_cards),
            new Deck($deck2_cards)
        ];
    }

    /**
     * Erstellt ein komplettes Test-Setup mit 2 Spielern
     */
    public static function create_test_game(): array
    {
        // Ein großes Deck erstellen
        $master_deck = self::create_random_deck(40);
        $master_deck->shuffle();

        // In zwei Decks aufteilen
        [$deck1, $deck2] = self::split_deck($master_deck);

        // Spieler erstellen
        $player1 = new Player('player1', 'unnamed1', $deck1);
        $player2 = new Player('player2', 'Aalmann', $deck2);

        return [$player1, $player2];
    }
}
