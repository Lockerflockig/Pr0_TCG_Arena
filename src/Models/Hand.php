<?php

namespace App\Models;

class Hand
{
    private array $hand = [];
    private const MAX_SIZE = 5;
    private Deck $deck;
    private Card $strongest_card;
    private Player $owner;
    public function __construct(Deck $deck, $owner)
    {
        $this->deck = $deck;
        $this->owner = $owner;
    }
    public function draw_hand(): void
    {
        $this->hand = [];
        for ($i = 0; $i < self::MAX_SIZE ; $i++)
        {
            $this->hand[] = $this->deck->draw_card();
            echo "drawing card $i ";
        }
    }
    public function get_strongest_trait(): Traits
    {
        //var_dump($this->hand);
        $this->strongest_card = $this->hand[0];
        $current_highest_val = $this->strongest_card->get_val($this->strongest_card->get_best_trait());
        $hand_size = count($this->hand);
        for ($i = 1; $i < $hand_size; $i++)
        {
            $next_card = $this->hand[$i];
            echo "DEBUG " . $next_card->get_best_trait()->name . " itr = $i "; // <<< RICHTIG
            $next_cards_highest_val = $next_card->get_val($next_card->get_best_trait());

            if ($current_highest_val < $next_cards_highest_val) {
                $current_highest_val = $next_cards_highest_val;
                $this->strongest_card = $next_card;
            }
        }

        return $this->strongest_card->get_best_trait();
    }
    public function get_highest_value_by_trait(Hand $hand, Traits $trait): Card
    {
        $current_strongest_Card = $hand->hand[0];
        $current_strongest_val = $current_strongest_Card->get_val($trait);
        $hand_size = count($hand->hand);
        for ($i = 1; $i < $hand_size; $i++)
        {
            $next_card = $this->hand[$i];
            $next_cards_highest_val = $next_card->get_val($trait);

            if ($current_strongest_val < $next_cards_highest_val)
            {
                $current_strongest_Card = $next_card;
            }
        }
        return $current_strongest_Card;
    }
    public function battle_hand(Player $enemy): Player
    {
        $enemy_hand = $enemy->hand;
        $strongest_trait = $this->get_strongest_trait();
        $strongest_enemy_card = $this->get_highest_value_by_trait($enemy_hand, $strongest_trait);
        $enemy_val = $strongest_enemy_card->get_val($strongest_trait);
        if ($enemy_val > $this->strongest_card->get_val($strongest_trait))
        {
            return $enemy;
        }
        return $this->owner;
    }
}