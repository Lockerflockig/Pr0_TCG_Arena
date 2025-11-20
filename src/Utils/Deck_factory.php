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
    public static function create_random_deck(int $size = 10): Deck
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
     * Erstellt ein komplettes Test-Setup mit 2 Spielern, basierend auf einem
     * geteilten Gesamtdeck.
     * * @return array<int, Player>
     */
    public static function create_test_setup(
        string $player1_name,
        string $player2_name,
        int $deck_size = 20
    ): array
    {
        $full_deck = self::create_random_deck($deck_size);
        $full_deck->shuffle();

        [$deck1, $deck2] = self::split_deck($full_deck);

        $player1 = new Player(
            id: 'P1-' . uniqid(),
            name: $player1_name,
            deck: $deck1
        );

        $player2 = new Player(
            id: 'P2-' . uniqid(),
            name: $player2_name,
            deck: $deck2
        );

        return [$player1, $player2];
    }

    /**
     * Erstellt ein Test-Spiel mit 2 Spielern und einem vollen Deck für die Game-Manager-Demo
     * * @return array<int, Player>
     */
    public static function create_test_game(): array
    {
        $full_deck = self::create_random_deck(Deck::MAX_SIZE * 2);
        $full_deck->shuffle();

        [$deck1, $deck2] = self::split_deck($full_deck);

        $player1 = new Player(
            id: 'P1-' . uniqid(),
            name: 'Panda',
            deck: $deck1
        );
        $player2 = new Player(
            id: 'P2-' . uniqid(),
            name: 'Tiger',
            deck: $deck2
        );

        return [$player1, $player2];
    }
}