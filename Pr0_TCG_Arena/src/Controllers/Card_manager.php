<?php

namespace App\Controllers;

use App\Models\Card;

class Card_manager
{
    private Card $card1;
    private Card $card2;
    public function add(Card $card): void
    {
        if (null === $this->card1) {
            $this->card1 = $card;
            return;
        }
        $this->card2 = $card;
    }
    public function fight(): void
    {
        if (null === $this->card2 && null === $this->card1)
        {
            return;
        }
        $player2 = $this->card2->card_holder;
        if (null === $player2->selected_trait)
        {
            return;
        }
        $player1 = $this->card1->card_holder;
        $p1_power = $this->card1->get_val($player1->selected_trait);
        $p2_power = $this->card2->get_val($player2->selected_trait);
        if ($p1_power === $p2_power)
        {
            $player1->draw_card();
            $player2->draw_card();
        }
        if ($p1_power > $p2_power)
        {
            $player2->draw_card();
        }
        if ($p1_power < $p2_power)
        {
            $player1->draw_card();
        }
    }
}