<?php

/** The Chess table: the two-player board game table with the rules of CasinoChessRules. */
class CasinoChessModel extends CasinoBoardGameModel
{
    protected static function rules(): string
    {
        return CasinoChessRules::class;
    }

    protected static function game(): string
    {
        return 'chess';
    }
}
