<?php

namespace App\Models;

class Player
{
    public readonly string $id;
    public readonly string $name;
    public Deck $deck;
    public Hand $hand;

    public ?Card $current_card = null;
    public function __construct(string $id, string $name, Deck $deck)
    {
        $this->id = $id;
        $this->name = $name;
        $this->deck = $deck;
    }
    public function draw_card(): void
    {
        $this->current_card = $this->deck->draw_card();
    }
    public function is_alive(): bool
    {
        return $this->deck->has_cards() || $this->current_card !== null;
    }
}