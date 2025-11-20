<?php

namespace App\Models;
class Deck
{
    private array $cards = [];
    public const MAX_SIZE = 20;
    public function __construct(array $cards = [])
    {
        $this->cards = $cards;
    }
    public function draw_card(): ?Card
    {
        if (empty($this->cards)) {
            return null;
        }
        return array_shift($this->cards);
    }
    public function add_card_to_bottom(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Fügt Karte oben zum Stapel hinzu (für Setup)
     */
    public function add_card_to_top(Card $card): void
    {
        array_unshift($this->cards, $card);
    }
    public function has_cards(): bool
    {
        return count($this->cards) > 0;
    }
    public function count(): int
    {
        return count($this->cards);
    }
    public function shuffle(): void
    {
        shuffle($this->cards);
    }
    public function get_all_cards(): array
    {
        return $this->cards;
    }
}