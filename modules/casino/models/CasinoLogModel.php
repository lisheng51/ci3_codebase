<?php

class CasinoLogModel
{
    use BasicModel;

    public const ACTIONS = [
        'register' => 'Nieuwe speler',
        'login' => 'Inloggen',
        'logout' => 'Uitloggen',
        'bonus' => 'Dagelijkse bonus',
        'admin' => 'Aanpassing beheerder',
        'join' => 'Aan tafel',
        'leave' => 'Van tafel',
        'bet' => 'Inzet',
        'refund' => 'Inzet terug',
        'double' => 'Verdubbelen',
        'hit' => 'Kaart',
        'stand' => 'Passen',
        'timeout' => 'Tijd voorbij',
        'roll' => 'Worp',
        'spin' => 'Draai',
        'deal' => 'Delen',
        'win' => 'Gewonnen',
        'lose' => 'Verloren',
        'push' => 'Gelijk',
        'blackjack' => 'Blackjack',
        'jackpot' => 'Jackpot',
        'buyin' => 'Inleg aan tafel',
        'cashout' => 'Terug naar saldo',
        'blind' => 'Blind',
        'fold' => 'Fold',
        'check' => 'Check',
        'call' => 'Call',
        'raise' => 'Raise',
        'allin' => 'All-in',
        'rake' => 'Rake',
        'line' => 'Lijn',
        'bingo' => 'Bingo',
        'bid' => 'Bod',
        'landlord' => 'Landheer',
        'play' => 'Kaarten gespeeld',
        'pass' => 'Pas',
    ];

    public static function __constructStatic()
    {
        self::$table = "casino_log";
        self::$primaryKey = "casino_log_id";
        self::$sqlOrderBy = ["casino_log_id" => 'desc'];
        self::$selectOrderBy = [
            'casino_log_id#desc' => 'ID (aflopend)',
            'casino_log_id#asc' => 'ID (oplopend)',
            'amount#desc' => 'Bedrag (aflopend)',
            'amount#asc' => 'Bedrag (oplopend)',
        ];
    }

    /**
     * One log line. $amount is the chip change for the player (negative for a bet), 0 for events.
     * $balanceAfter < 0 means: read the current balance of the player.
     */
    public static function write(int $userId, string $action, int $amount = 0, string $detail = '', int $tableId = 0, string $game = '', int $roundNo = 0, int $balanceAfter = -1): int
    {
        if ($balanceAfter < 0) {
            $balanceAfter = $userId > 0 ? CasinoUserModel::balance($userId) : 0;
        }
        CIDb()->insert(self::$table, [
            'casino_user_id' => $userId,
            'casino_table_id' => $tableId,
            'game' => $game,
            'round_no' => $roundNo,
            'action' => $action,
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'detail' => mb_substr($detail, 0, 1000),
            'ip' => (string)CIInput()->ip_address(),
            'createdby' => LoginModel::userId(),
        ]);
        return (int)CIDb()->insert_id();
    }

    /**
     * The last settled rounds of a player at a table: [['round', 'bet', 'ret', 'net', 'outcome'], ...], newest first.
     * bet = bets and doubles minus refunds, ret = what was paid out.
     * Round numbers are not unique over time (solo rounds count per player and start at 1, also after a table
     * was switched to solo), so the rows are read in log order: bets add up until the result of that round closes it.
     */
    public static function recentRounds(int $userId, int $tableId, int $currentRound = 0, int $limit = 10): array
    {
        $rows = CIDb()->query(
            "SELECT casino_log_id, round_no, action, amount, game, detail
             FROM " . CIDb()->dbprefix(self::$table) . "
             WHERE casino_table_id = ? AND casino_user_id = ?
               AND action IN ('bet', 'double', 'refund', 'win', 'lose', 'push', 'blackjack')
             ORDER BY casino_log_id DESC
             LIMIT " . max(100, $limit * 60),
            [$tableId, $userId]
        )->result_array();

        $open = [];   // round number => chips bet so far
        $rounds = [];
        foreach (array_reverse($rows) as $r) {
            $no = (int)$r['round_no'];
            if (in_array($r['action'], ['bet', 'double', 'refund'], true)) {
                $open[$no] = ($open[$no] ?? 0) - (int)$r['amount'];
                continue;
            }
            $bet = $open[$no] ?? 0;
            unset($open[$no]);
            $rounds[] = [
                'round' => $no,
                'bet' => $bet,
                'ret' => (int)$r['amount'],
                'net' => (int)$r['amount'] - $bet,
                'outcome' => $r['action'],
                'result' => self::resultOf((string)$r['game'], (string)$r['detail']),
            ];
        }
        return array_slice(array_reverse($rounds), 0, max(1, $limit));
    }

    /**
     * The result of a round, read from the detail of its settlement line (as written by the game models),
     * so every round in the records can show it, also rounds that are no longer in the table's history.
     */
    public static function resultOf(string $game, string $detail): array
    {
        switch ($game) {
            case 'dice': // "6-2-4 (12): ..."
                if (preg_match('/^([1-6])-([1-6])-([1-6]) \((\d+)\)/', $detail, $m)) {
                    return ['dice' => [(int)$m[1], (int)$m[2], (int)$m[3]], 'total' => (int)$m[4]];
                }
                break;
            case 'roulette': // "17 black: ..."
                if (preg_match('/^(\d+) (red|black|green)/', $detail, $m)) {
                    return ['number' => (int)$m[1], 'color' => $m[2]];
                }
                break;
            case 'blackjack': // "Inzet 100, speler AS KD (21) - bank 10H 9S (19)"
                if (preg_match('/speler .*?\((\d+)\) - bank .*?\((\d+)\)/', $detail, $m)) {
                    return ['player' => (int)$m[1], 'bank' => (int)$m[2]];
                }
                break;
            case 'slot': // "bell bell bell: lijn 1 x50 = 500"
                if (preg_match('/^([a-z]+) ([a-z]+) ([a-z]+)/', $detail, $m) && !array_diff(array_slice($m, 1), CasinoSlotModel::SYMBOLS)) {
                    return ['symbols' => array_slice($m, 1)];
                }
                break;
            case 'holdem': // "AS KD | 2H 7C 9D JS QH | two_pair" (or "| fold")
                $parts = array_map('trim', explode('|', $detail));
                $hand = $parts[2] ?? '';
                if (in_array($hand, CasinoHoldemModel::HANDS, true)) {
                    return ['hand' => $hand];
                }
                if ($hand === 'fold' || ($parts[3] ?? '') === 'fold') {
                    return ['folded' => true];
                }
                break;
            case 'bingo': // "33 ballen"
                if (preg_match('/^(\d+) ballen/', $detail, $m)) {
                    return ['balls' => (int)$m[1]];
                }
                break;
            case 'chess':
            case 'xiangqi': // "white checkmate 67": the colour played, how the game ended, the number of moves
                if (preg_match('/^(white|black) ([a-z_]+) (\d+)/', $detail, $m)) {
                    return ['color' => $m[1], 'reason' => $m[2], 'plies' => (int)$m[3]];
                }
                break;
            case 'doudizhu': // "landlord x6 spring | bod 3, bommen 1 | stack 900"
                if (preg_match('/^(landlord|farmer) x(\d+)( spring)?/', $detail, $m)) {
                    return ['role' => $m[1], 'mult' => (int)$m[2], 'spring' => !empty($m[3])];
                }
                break;
        }
        return [];
    }

    public static function actionLabel(string $action): string
    {
        $text = CasinoLang::t('action_' . $action);
        return $text !== 'action_' . $action ? $text : (self::ACTIONS[$action] ?? $action);
    }
}
