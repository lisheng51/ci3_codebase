<?php

/** The Xiangqi (中国象棋) table: the two-player board game table with the rules of CasinoXiangqiRules. */
class CasinoXiangqiModel extends CasinoBoardGameModel
{
    protected static function rules(): string
    {
        return CasinoXiangqiRules::class;
    }

    protected static function game(): string
    {
        return 'xiangqi';
    }
}
