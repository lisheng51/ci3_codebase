<?php

/**
 * Dice (Sic Bo) with three dice. All players at a table bet on the same roll.
 * Round: betting (the timer starts with the first bet of the round) -> roll + pay out -> result -> next round.
 *
 * State: phase, round, until (timestamp, 0 = waiting for the first bet), bets [uid => [spot => amount]],
 * dice, total, results [uid => [bet, ret]], history (last rolls), online [uid => [n, t]].
 */
class CasinoDiceModel
{
    public const HISTORY = 15;

    public static function normalize(array $state, array $table): array
    {
        return array_merge([
            'phase' => 'betting',
            'round' => 1,
            'until' => 0,
            'bets' => [],
            'names' => [],
            'dice' => [],
            'total' => 0,
            'results' => [],
            'history' => [],
            'order' => [], // uid => [[spot, amount], ...] in the order they were placed (for "undo last bet")
            'online' => [],
        ], $state);
    }

    public static function tick(array $table, array &$state): void
    {
        CasinoTableModel::pruneOnline($state);
        $now = time();
        if ($state['phase'] === 'betting' && $state['until'] > 0 && $now >= $state['until']) {
            self::roll($table, $state);
        } elseif ($state['phase'] === 'result' && $now >= $state['until']) {
            self::nextRound($state);
        }
    }

    private static function nextRound(array &$state): void
    {
        $state['phase'] = 'betting';
        $state['round']++;
        $state['until'] = 0;
        $state['bets'] = [];
        $state['names'] = [];
        $state['results'] = [];
        $state['order'] = [];
    }

    /**
     * Solo table: $state is this player's own round. A bet after a result starts the next round;
     * 'go' settles at once (no timer, nobody else).
     */
    public static function soloAct(array $table, array &$state, array $user, string $action, array $post): void
    {
        if ($action === 'go') {
            if ($state['phase'] !== 'betting' || empty($state['bets'])) {
                throw new CasinoException(CasinoLang::t('err_solo_no_bets'));
            }
            self::roll($table, $state);
        } else {
            if ($action === 'bet' && $state['phase'] === 'result') {
                self::nextRound($state);
            }
            self::act($table, $state, $user, $action, $post);
        }
        $state['until'] = 0; // no timers when playing solo
    }

    /**
     * "big", "small", "odd", "even", "triple" (any triple), "single_1".."single_6", "total_4".."total_17",
     * "double_1".."double_6" (that number on at least two dice), "triple_1".."triple_6" (that number on all three dice),
     * "combo_1_2".."combo_5_6" (both numbers show; the lowest first).
     */
    public static function validSpot(string $spot): bool
    {
        if (in_array($spot, ['big', 'small', 'odd', 'even', 'triple'], true)) {
            return true;
        }
        if (preg_match('/^(single|double|triple)_[1-6]$/', $spot)) {
            return true;
        }
        if (preg_match('/^combo_([1-6])_([1-6])$/', $spot, $m)) {
            return (int)$m[1] < (int)$m[2];
        }
        if (preg_match('/^total_(\d+)$/', $spot, $m)) {
            return isset(CasinoConfigModel::diceTotals()[(int)$m[1]]);
        }
        return false;
    }

    public static function spotLabel(string $spot): string
    {
        $labels = ['big' => 'Groot', 'small' => 'Klein', 'odd' => 'Oneven', 'even' => 'Even', 'triple' => 'Willekeurige triple'];
        if (isset($labels[$spot])) {
            return $labels[$spot];
        }
        $parts = explode('_', $spot);
        $names = ['single' => 'Getal', 'total' => 'Totaal', 'double' => 'Dubbel', 'triple' => 'Triple', 'combo' => 'Combinatie'];
        return ($names[$parts[0]] ?? $parts[0]) . ' ' . implode('+', array_slice($parts, 1));
    }

    public static function act(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        if ($action === 'bet') {
            if ($state['phase'] !== 'betting') {
                throw new CasinoException(CasinoLang::t('err_wait_round'));
            }
            $spot = (string)($post['spot'] ?? '');
            $amount = (int)($post['amount'] ?? 0);
            if (!self::validSpot($spot)) {
                throw new CasinoException(CasinoLang::t('err_unknown_spot'));
            }
            if ($amount < (int)$table['min_bet']) {
                throw new CasinoException(CasinoLang::t('err_min_bet', ['amount' => (int)$table['min_bet']]));
            }
            $already = (int)($state['bets'][$uid][$spot] ?? 0);
            if ($already + $amount > (int)$table['max_bet']) {
                throw new CasinoException(CasinoLang::t('err_max_spot', ['amount' => (int)$table['max_bet']]));
            }
            $balance = CasinoUserModel::debit($uid, $amount);
            $state['bets'][$uid][$spot] = $already + $amount;
            $state['order'][$uid][] = [$spot, $amount];
            $state['names'][$uid] = CasinoUserModel::publicName($user);
            if ($state['until'] === 0) {
                $state['until'] = time() + CasinoConfigModel::int('casino_dice_bet_seconds', 3, 600);
            }
            CasinoLogModel::write($uid, 'bet', -$amount, self::spotLabel($spot), (int)$table['casino_table_id'], 'dice', $state['round'], $balance);
            return;
        }

        if ($action === 'undo') {
            // takes back the last chip(s) the player put down
            $last = $state['phase'] === 'betting' && !empty($state['order'][$uid]) ? array_pop($state['order'][$uid]) : null;
            if (!$last || empty($state['bets'][$uid][$last[0]])) {
                throw new CasinoException(CasinoLang::t('err_nothing_to_clear'));
            }
            [$spot, $amount] = $last;
            $amount = min($amount, (int)$state['bets'][$uid][$spot]);
            $state['bets'][$uid][$spot] -= $amount;
            if ($state['bets'][$uid][$spot] <= 0) {
                unset($state['bets'][$uid][$spot]);
            }
            if (empty($state['bets'][$uid])) {
                unset($state['bets'][$uid], $state['names'][$uid], $state['order'][$uid]);
                if (empty($state['bets'])) {
                    $state['until'] = 0;
                }
            }
            $balance = CasinoUserModel::refund($uid, $amount);
            CasinoLogModel::write($uid, 'refund', $amount, self::spotLabel((string)$spot), (int)$table['casino_table_id'], 'dice', $state['round'], $balance);
            return;
        }

        if ($action === 'clear' || $action === 'leave') {
            if ($state['phase'] !== 'betting' || empty($state['bets'][$uid])) {
                if ($action === 'clear') {
                    throw new CasinoException(CasinoLang::t('err_nothing_to_clear'));
                }
                unset($state['online'][$uid]);
                return;
            }
            $amount = array_sum($state['bets'][$uid]);
            $detail = self::betsText($state['bets'][$uid]);
            unset($state['bets'][$uid], $state['names'][$uid], $state['order'][$uid]);
            if (empty($state['bets'])) {
                $state['until'] = 0;
            }
            if ($action === 'leave') {
                unset($state['online'][$uid]);
            }
            $balance = CasinoUserModel::refund($uid, $amount);
            CasinoLogModel::write($uid, 'refund', $amount, $detail, (int)$table['casino_table_id'], 'dice', $state['round'], $balance);
            return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    private static function roll(array $table, array &$state): void
    {
        $dice = [random_int(1, 6), random_int(1, 6), random_int(1, 6)];
        $total = array_sum($dice);
        $triple = $dice[0] === $dice[1] && $dice[1] === $dice[2];
        $tableId = (int)$table['casino_table_id'];

        $state['dice'] = $dice;
        $state['total'] = $total;
        $state['results'] = [];
        foreach ($state['bets'] as $uid => $bets) {
            $uid = (int)$uid;
            $ret = 0;
            $lines = [];
            foreach ($bets as $spot => $amount) {
                $odds = self::odds((string)$spot, $dice, $total, $triple);
                $back = $odds > 0 ? $amount + (int)floor($amount * $odds) : 0;
                $ret += $back;
                $lines[] = self::spotLabel((string)$spot) . ' ' . $amount . ' > ' . $back;
            }
            $bet = array_sum($bets);
            $balance = CasinoUserModel::credit($uid, $ret, max(0, $ret - $bet));
            $state['results'][$uid] = ['bet' => $bet, 'ret' => $ret];
            $action = $ret > $bet ? 'win' : ($ret === $bet ? 'push' : 'lose');
            CasinoLogModel::write($uid, $action, $ret, implode('-', $dice) . ' (' . $total . '): ' . implode('; ', $lines), $tableId, 'dice', $state['round'], $balance);
        }
        CasinoLogModel::write(0, 'roll', 0, implode('-', $dice) . ' (' . $total . '), spelers: ' . count($state['bets']), $tableId, 'dice', $state['round'], 0);

        array_unshift($state['history'], ['round' => $state['round'], 'dice' => $dice, 'total' => $total]);
        $state['history'] = array_slice($state['history'], 0, self::HISTORY);
        $state['phase'] = 'result';
        $state['until'] = time() + CasinoConfigModel::int('casino_dice_result_seconds', 2, 120);
    }

    /** Winnings per chip (x : 1), 0 when the spot loses. */
    public static function odds(string $spot, array $dice, int $total, bool $triple): float
    {
        switch ($spot) {
            case 'big':
                return !$triple && $total >= 11 ? CasinoConfigModel::float('casino_dice_payout_bigsmall') : 0;
            case 'small':
                return !$triple && $total <= 10 ? CasinoConfigModel::float('casino_dice_payout_bigsmall') : 0;
            case 'odd':
                return !$triple && $total % 2 === 1 ? CasinoConfigModel::float('casino_dice_payout_oddeven') : 0;
            case 'even':
                return !$triple && $total % 2 === 0 ? CasinoConfigModel::float('casino_dice_payout_oddeven') : 0;
            case 'triple':
                return $triple ? CasinoConfigModel::float('casino_dice_payout_triple') : 0;
        }
        if (preg_match('/^single_([1-6])$/', $spot, $m)) {
            // on 1 die 1:1, on 2 dice and on all 3 dice as set (standard 2 and 3, Holland Casino 2 and 12)
            $count = count(array_keys($dice, (int)$m[1], true));
            return [0, 1.0, CasinoConfigModel::float('casino_dice_payout_single2'), CasinoConfigModel::float('casino_dice_payout_single3')][$count];
        }
        if (preg_match('/^total_(\d+)$/', $spot, $m) && (int)$m[1] === $total) {
            return (float)(CasinoConfigModel::diceTotals()[$total] ?? 0);
        }
        $count = fn(int $n) => count(array_keys($dice, $n, true));
        if (preg_match('/^double_([1-6])$/', $spot, $m)) {
            return $count((int)$m[1]) >= 2 ? CasinoConfigModel::float('casino_dice_payout_double') : 0;
        }
        if (preg_match('/^triple_([1-6])$/', $spot, $m)) {
            return $count((int)$m[1]) === 3 ? CasinoConfigModel::float('casino_dice_payout_specific_triple') : 0;
        }
        if (preg_match('/^combo_([1-6])_([1-6])$/', $spot, $m)) {
            return $count((int)$m[1]) > 0 && $count((int)$m[2]) > 0 ? CasinoConfigModel::float('casino_dice_payout_combo') : 0;
        }
        return 0;
    }

    private static function betsText(array $bets): string
    {
        $parts = [];
        foreach ($bets as $spot => $amount) {
            $parts[] = self::spotLabel((string)$spot) . ' ' . $amount;
        }
        return implode('; ', $parts);
    }

    public static function refundAll(array $table, array &$state): void
    {
        if ($state['phase'] !== 'betting') {
            return; // the bets of the last roll are already paid out
        }
        foreach ($state['bets'] as $uid => $bets) {
            $amount = array_sum($bets);
            $balance = CasinoUserModel::refund((int)$uid, $amount);
            CasinoLogModel::write((int)$uid, 'refund', $amount, 'Tafel gereset: ' . self::betsText($bets), (int)$table['casino_table_id'], 'dice', $state['round'], $balance);
        }
        $state['bets'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $players = [];
        foreach ($state['online'] as $id => $row) {
            $players[(int)$id] = ['name' => $row['n'], 'bet' => 0, 'ret' => null, 'me' => (int)$id === $uid];
        }
        foreach ($state['bets'] as $id => $bets) {
            $players[(int)$id] = ($players[(int)$id] ?? ['name' => $state['names'][$id] ?? '?', 'ret' => null, 'me' => (int)$id === $uid]);
            $players[(int)$id]['bet'] = array_sum($bets);
        }
        foreach ($state['results'] as $id => $res) {
            if (isset($players[(int)$id])) {
                $players[(int)$id]['ret'] = $res['ret'];
            }
        }

        $totals = [];
        foreach (CasinoConfigModel::diceTotals() as $total => $odds) {
            $totals[] = ['total' => $total, 'odds' => $odds];
        }

        return [
            'game' => 'dice',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => CasinoTableModel::remaining($state),
            'dice' => $state['phase'] === 'result' ? $state['dice'] : [],
            'total' => $state['phase'] === 'result' ? $state['total'] : 0,
            'history' => $state['history'],
            'myBets' => (object)($state['bets'][$uid] ?? []),
            'canUndo' => $state['phase'] === 'betting' && !empty($state['order'][$uid]),
            'myResult' => $state['results'][$uid] ?? null,
            'players' => array_values($players),
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => (int)$table['min_bet'],
            'maxBet' => (int)$table['max_bet'],
            'payouts' => [
                'bigsmall' => CasinoConfigModel::float('casino_dice_payout_bigsmall'),
                'oddeven' => CasinoConfigModel::float('casino_dice_payout_oddeven'),
                'triple' => CasinoConfigModel::float('casino_dice_payout_triple'),
                'double' => CasinoConfigModel::float('casino_dice_payout_double'),
                'specificTriple' => CasinoConfigModel::float('casino_dice_payout_specific_triple'),
                'combo' => CasinoConfigModel::float('casino_dice_payout_combo'),
                'single2' => CasinoConfigModel::float('casino_dice_payout_single2'),
                'single3' => CasinoConfigModel::float('casino_dice_payout_single3'),
                'totals' => $totals,
            ],
        ];
    }
}
