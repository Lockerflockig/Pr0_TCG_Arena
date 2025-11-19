<?php

namespace App\Models;

use App\Models\Traits\Traits;

class Player
{
    public readonly int $id;
    public readonly string $name;
    public Deck $deck;
    public Card $selected_card;
    public Traits $selected_trait;
    public bool $is_alive;
    public $hand;
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->is_alive = true;
        $this->selected_trait = null;
    }
    public function draw_card(): void
    {
        if (!$this->deck->has_cards()) {
            $this->is_alive = false;
        }
        $this->selected_card = $this->deck->draw_card();
    }
    public function select_trait(Traits $trait): void
    {
        $this->selected_trait = $trait;
    }
}