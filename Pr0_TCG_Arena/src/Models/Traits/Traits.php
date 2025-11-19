<?php

namespace App\Models\Traits;

enum Traits: string
{
    case REP = 'rep';
    case DIP = 'dip';
    case AGG = 'agg';
    case END = 'end';
    case TAC = 'tac';
    case ACT = 'act';

    public static function get_random_trait(): Traits
    {
        return Traits::cases()[array_rand(Traits::cases())];
    }
}