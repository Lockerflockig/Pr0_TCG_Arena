<?php

namespace App\Models;
use App\Models\Traits\Traits;

readonly class Card
{
    public Player $card_holder;
    private string $name;
    private int $id;
    private int $rep;
    private int $dip;
    private int $agg;
    private int $end;
    private int $tac;
    private int $act;
    public function __construct(
        Player $card_holder, string $name,int $id, int $rep, int $dip, int $agg, int $end, int $tac, int $act)
    {
        $this->card_holder = $card_holder;
        $this->name = $name;
        $this->id = $id;
        $this->rep = $rep;
        $this->dip = $dip;
        $this->agg = $agg;
        $this->end = $end;
        $this->tac = $tac;
        $this->act = $act;
    }
    public function get_val(Traits $trait): int
    {
        return match ($trait) {
            Traits::REP => $this->rep,
            Traits::DIP => $this->dip,
            Traits::AGG => $this->agg,
            Traits::END => $this->end,
            Traits::TAC => $this->tac,
            Traits::ACT => $this->act,
        };
    }
    public function get_name(): string
    {
        return $this->name;
    }
    public function get_id(): int
    {
        return $this->id;
    }
}
