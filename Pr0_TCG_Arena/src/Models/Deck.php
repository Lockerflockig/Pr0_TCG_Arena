<?php

namespace App\Models;

class Deck
{
    public $cards;
    public final int $max_size = 20;
    public function draw_card(): ?Card
    {
        if (empty($this->cards)) {
            return null;
        }
        $drawn_card = $this->cards[random_int(0, $this->cards->count())];
        unset($this->cards[$drawn_card]);
        return $drawn_card;
    }
    public function has_cards(): bool
    {
        return count($this->cards) > 0;
    }
    public function add_card(Card $card): void
    {

    }
}