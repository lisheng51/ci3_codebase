<?php

/**
 * European roulette (single zero). All players at a table bet on the same spin.
 * Round: betting (the timer starts with the first bet) -> spin + pay out -> result (long enough for the wheel animation) -> next round.
 * Bets: a single number (n_0..n_36, 35:1), red/black, odd/even, low (1-18)/high (19-36) at 1:1,
 * dozens (dozen_1..3) and columns (column_1..3) at 2:1. The zero loses every outside bet.
 *
 * State: phase, round, until, bets [uid => [spot => amount]], names, number, results [uid => [bet, ret]], history, online.
 */
class CasinoRouletteModel
{
    public const HISTORY = 20;
    public const RED = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
    /** The pockets in the order of the European wheel, clockwise from the zero. */
    public const WHEEL = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];

    public static function normalize(array $state, array $table): array
    {
        return array_merge([
            'phase' => 'betting',
            'round' => 1,
            'until' => 0,
            'bets' => [],
            'names' => [],
            'number' => null,
            'results' => [],
            'history' => [],
            'online' => [],
        ], $state);
    }

    public static function tick(array $table, array &$state): void
    {
        CasinoTableModel::pruneOnline($state);
        $now = time();
        if ($state['phase'] === 'betting' && $state['until'] > 0 && $now >= $state['until']) {
            self::spin($table, $state);
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
            self::spin($table, $state);
        } else {
            if ($action === 'bet' && $state['phase'] === 'result') {
                self::nextRound($state);
            }
            self::act($table, $state, $user, $action, $post);
        }
        $state['until'] = 0; // no timers when playing solo
    }

    public static function color(int $n): string
    {
        return $n === 0 ? 'green' : (in_array($n, self::RED, true) ? 'red' : 'black');
    }

    public static function validSpot(string $spot): bool
    {
        if (in_array($spot, ['red', 'black', 'odd', 'even', 'low', 'high'], true)) {
            return true;
        }
        if (preg_match('/^(dozen|column)_([1-3])$/', $spot)) {
            return true;
        }
        return preg_match('/^n_(\d+)$/', $spot, $m) === 1 && (int)$m[1] <= 36 && $m[1] === (string)(int)$m[1];
    }

    /** Winnings per chip (x : 1), 0 when the spot loses. */
    public static function odds(string $spot, int $n): int
    {
        if (preg_match('/^n_(\d+)$/', $spot, $m)) {
            return (int)$m[1] === $n ? 35 : 0;
        }
        if ($n === 0) {
            return 0;
        }
        switch ($spot) {
            case 'red':
                return self::color($n) === 'red' ? 1 : 0;
            case 'black':
                return self::color($n) === 'black' ? 1 : 0;
            case 'odd':
                return $n % 2 === 1 ? 1 : 0;
            case 'even':
                return $n % 2 === 0 ? 1 : 0;
            case 'low':
                return $n <= 18 ? 1 : 0;
            case 'high':
                return $n >= 19 ? 1 : 0;
        }
        if (preg_match('/^dozen_([1-3])$/', $spot, $m)) {
            return (int)ceil($n / 12) === (int)$m[1] ? 2 : 0;
        }
        if (preg_match('/^column_([1-3])$/', $spot, $m)) {
            return (($n - 1) % 3) + 1 === (int)$m[1] ? 2 : 0;
        }
        return 0;
    }

    public static function spotLabel(string $spot): string
    {
        if (preg_match('/^n_(\d+)$/', $spot, $m)) {
            return (string)$m[1];
        }
        $labels = ['red' => 'Rood', 'black' => 'Zwart', 'odd' => 'Oneven', 'even' => 'Even', 'low' => '1-18', 'high' => '19-36'];
        if (isset($labels[$spot])) {
            return $labels[$spot];
        }
        [$type, $value] = explode('_', $spot, 2);
        return ($type === 'dozen' ? 'Dozijn ' : 'Kolom ') . $value;
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
            $state['names'][$uid] = CasinoUserModel::publicName($user);
            if ($state['until'] === 0) {
                $state['until'] = time() + CasinoConfigModel::int('casino_roulette_bet_seconds', 3, 600);
            }
            CasinoLogModel::write($uid, 'bet', -$amount, self::spotLabel($spot), (int)$table['casino_table_id'], 'roulette', $state['round'], $balance);
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
            unset($state['bets'][$uid], $state['names'][$uid]);
            if (empty($state['bets'])) {
                $state['until'] = 0;
            }
            if ($action === 'leave') {
                unset($state['online'][$uid]);
            }
            $balance = CasinoUserModel::refund($uid, $amount);
            CasinoLogModel::write($uid, 'refund', $amount, $detail, (int)$table['casino_table_id'], 'roulette', $state['round'], $balance);
            return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    private static function spin(array $table, array &$state): void
    {
        $n = random_int(0, 36);
        $tableId = (int)$table['casino_table_id'];
        $state['number'] = $n;
        $state['results'] = [];
        foreach ($state['bets'] as $uid => $bets) {
            $uid = (int)$uid;
            $ret = 0;
            $lines = [];
            foreach ($bets as $spot => $amount) {
                $odds = self::odds((string)$spot, $n);
                $back = $odds > 0 ? $amount * ($odds + 1) : 0;
                $ret += $back;
                $lines[] = self::spotLabel((string)$spot) . ' ' . $amount . ' > ' . $back;
            }
            $bet = array_sum($bets);
            $balance = CasinoUserModel::credit($uid, $ret, max(0, $ret - $bet));
            $state['results'][$uid] = ['bet' => $bet, 'ret' => $ret];
            $action = $ret > $bet ? 'win' : ($ret === $bet ? 'push' : 'lose');
            CasinoLogModel::write($uid, $action, $ret, $n . ' ' . self::color($n) . ': ' . implode('; ', $lines), $tableId, 'roulette', $state['round'], $balance);
        }
        CasinoLogModel::write(0, 'spin', 0, $n . ' ' . self::color($n) . ', spelers: ' . count($state['bets']), $tableId, 'roulette', $state['round'], 0);

        array_unshift($state['history'], ['round' => $state['round'], 'number' => $n]);
        $state['history'] = array_slice($state['history'], 0, self::HISTORY);
        $state['phase'] = 'result';
        $state['until'] = time() + CasinoConfigModel::int('casino_roulette_result_seconds', 5, 120);
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
            return; // the bets of the last spin are already paid out
        }
        foreach ($state['bets'] as $uid => $bets) {
            $amount = array_sum($bets);
            $balance = CasinoUserModel::refund((int)$uid, $amount);
            CasinoLogModel::write((int)$uid, 'refund', $amount, 'Tafel gereset: ' . self::betsText($bets), (int)$table['casino_table_id'], 'roulette', $state['round'], $balance);
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
            $players[(int)$id] = $players[(int)$id] ?? ['name' => $state['names'][$id] ?? '?', 'ret' => null, 'me' => (int)$id === $uid];
            $players[(int)$id]['bet'] = array_sum($bets);
        }
        foreach ($state['results'] as $id => $res) {
            if (isset($players[(int)$id])) {
                $players[(int)$id]['ret'] = $res['ret'];
            }
        }

        $result = $state['phase'] === 'result' ? (int)$state['number'] : null;
        return [
            'game' => 'roulette',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => CasinoTableModel::remaining($state),
            'number' => $result,
            'color' => $result === null ? null : self::color($result),
            'history' => array_map(fn($h) => $h + ['color' => self::color((int)$h['number'])], $state['history']),
            'myBets' => (object)($state['bets'][$uid] ?? []),
            'myResult' => $state['results'][$uid] ?? null,
            'players' => array_values($players),
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => (int)$table['min_bet'],
            'maxBet' => (int)$table['max_bet'],
        ];
    }
}
