<?php

/**
 * Fruit machine (fruitautomaat): 3 reels x 3 rows and 5 win lines. Every player spins on their own;
 * all players at one machine share a progressive jackpot that is won with 777 on the middle line.
 * The stop of every reel is drawn with random_int on the server; the browser only animates the result.
 *
 * Stake per spin = line bet x the number of lines the player chose (1-5, from line 1). A played line pays multiplier x line bet: three equal symbols, or cherries from the left.
 * Each stake adds casino_slot_jackpot_percent to the jackpot. The jackpot winner gets the whole pot at the
 * table's maximum line bet, otherwise a part in proportion to the line bet; the rest stays in the pot.
 *
 * State: round (spin counter; the round_no of the log), jackpot, history (recent wins, for everybody),
 * last [uid => that player's last spin], online.
 */
class CasinoSlotModel
{
    public const SYMBOLS = ['cherry', 'lemon', 'orange', 'plum', 'bell', 'bar', 'seven'];

    /** The reel strips from top to bottom; how often a symbol is on a strip is its chance. */
    public const REELS = [
        ['cherry', 'bell', 'lemon', 'cherry', 'plum', 'bell', 'lemon', 'bar', 'cherry', 'orange', 'seven', 'orange', 'plum', 'bar', 'lemon', 'cherry', 'lemon', 'plum', 'bell', 'cherry', 'orange', 'cherry', 'plum', 'orange', 'lemon', 'orange', 'cherry', 'plum', 'bell', 'lemon'],
        ['orange', 'lemon', 'plum', 'orange', 'cherry', 'bar', 'lemon', 'bell', 'lemon', 'cherry', 'lemon', 'bell', 'plum', 'cherry', 'orange', 'plum', 'bell', 'seven', 'orange', 'lemon', 'bell', 'plum', 'cherry', 'lemon', 'bar', 'orange', 'plum', 'bar', 'orange', 'cherry'],
        ['bell', 'plum', 'lemon', 'cherry', 'lemon', 'bell', 'lemon', 'bell', 'plum', 'bar', 'orange', 'lemon', 'orange', 'plum', 'orange', 'lemon', 'bar', 'plum', 'orange', 'cherry', 'plum', 'seven', 'lemon', 'cherry', 'lemon', 'cherry', 'orange', 'bar', 'plum', 'orange'],
    ];

    /** Win lines: the row (0 top, 1 middle, 2 bottom) on each reel. The first one is the middle line (jackpot line). */
    public const LINES = [[1, 1, 1], [0, 0, 0], [2, 2, 2], [0, 1, 2], [2, 1, 0]];

    public const HISTORY = 12;

    public static function normalize(array $state, array $table): array
    {
        return array_merge([
            'round' => 0,
            'jackpot' => (float)CasinoConfigModel::int('casino_slot_jackpot_seed'),
            'history' => [],
            'last' => [],
            'online' => [],
        ], $state);
    }

    public static function tick(array $table, array &$state): void
    {
        $online = CasinoTableModel::pruneOnline($state);
        foreach (array_keys($state['last']) as $uid) {
            if (!in_array((int)$uid, $online, true)) {
                unset($state['last'][$uid]);
            }
        }
    }

    /** The symbols in view: grid[reel][row], row 1 is the middle row (the stop). */
    public static function grid(array $stops): array
    {
        $grid = [];
        foreach (self::REELS as $r => $reel) {
            $n = count($reel);
            for ($row = 0; $row < 3; $row++) {
                $grid[$r][$row] = $reel[((int)$stops[$r] + $row - 1 + $n) % $n];
            }
        }
        return $grid;
    }

    /** [multiplier, number of symbols from the left that count] for one line; [0, 0] when it does not pay. */
    public static function linePay(array $grid, array $line, array $pay): array
    {
        $s = [$grid[0][$line[0]], $grid[1][$line[1]], $grid[2][$line[2]]];
        if ($s[0] === $s[1] && $s[1] === $s[2]) {
            return [(float)($pay[$s[0]] ?? 0), 3];
        }
        if ($s[0] === 'cherry' && $s[1] === 'cherry') {
            return [(float)($pay['cherry2'] ?? 0), 2];
        }
        if ($s[0] === 'cherry') {
            return [(float)($pay['cherry1'] ?? 0), 1];
        }
        return [0.0, 0];
    }

    /** Exact return of the line wins in percent (all stop combinations), without the jackpot. */
    public static function rtp(?array $pay = null): float
    {
        $pay = $pay ?? CasinoConfigModel::slotPaytable();
        $n = array_map('count', self::REELS);
        $total = 0.0;
        for ($a = 0; $a < $n[0]; $a++) {
            for ($b = 0; $b < $n[1]; $b++) {
                for ($c = 0; $c < $n[2]; $c++) {
                    $grid = self::grid([$a, $b, $c]);
                    foreach (self::LINES as $line) {
                        $total += self::linePay($grid, $line, $pay)[0];
                    }
                }
            }
        }
        return $total / ($n[0] * $n[1] * $n[2]) / count(self::LINES) * 100;
    }

    public static function act(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        if ($action === 'leave') {
            unset($state['online'][$uid], $state['last'][$uid]);
            return;
        }
        if ($action !== 'spin') {
            throw new CasinoException(CasinoLang::t('err_unknown_action'));
        }

        $lineBet = (int)($post['amount'] ?? 0);
        if ($lineBet < (int)$table['min_bet']) {
            throw new CasinoException(CasinoLang::t('err_min_bet', ['amount' => (int)$table['min_bet']]));
        }
        if ($lineBet > (int)$table['max_bet']) {
            throw new CasinoException(CasinoLang::t('err_max_bet', ['amount' => (int)$table['max_bet']]));
        }
        $tableId = (int)$table['casino_table_id'];
        // the player chooses how many lines play (1-5, always from line 1, the middle line)
        $lines = max(1, min(count(self::LINES), (int)($post['lines'] ?? 0) ?: count(self::LINES)));
        $stake = $lineBet * $lines;
        $balance = CasinoUserModel::debit($uid, $stake);

        $state['round']++;
        $round = $state['round'];
        CasinoLogModel::write($uid, 'bet', -$stake, $lineBet . ' x ' . $lines . ' lijnen', $tableId, 'slot', $round, $balance);
        $state['jackpot'] = round((float)$state['jackpot'] + $stake * CasinoConfigModel::float('casino_slot_jackpot_percent', 0, 50) / 100, 2);

        $stops = [];
        foreach (self::REELS as $reel) {
            $stops[] = random_int(0, count($reel) - 1);
        }
        $grid = self::grid($stops);
        $pay = CasinoConfigModel::slotPaytable();
        $wins = [];
        $lineWin = 0;
        foreach (array_slice(self::LINES, 0, $lines) as $i => $line) {
            [$mult, $count] = self::linePay($grid, $line, $pay);
            if ($mult > 0) {
                $amount = (int)floor($mult * $lineBet);
                $wins[] = ['line' => $i, 'count' => $count, 'mult' => $mult, 'amount' => $amount];
                $lineWin += $amount;
            }
        }

        $jackpotWin = 0;
        if ($grid[0][1] === 'seven' && $grid[1][1] === 'seven' && $grid[2][1] === 'seven') {
            $share = min(1, $lineBet / max(1, (int)$table['max_bet']));
            $jackpotWin = (int)floor((float)$state['jackpot'] * $share);
            $state['jackpot'] = max((float)CasinoConfigModel::int('casino_slot_jackpot_seed'), (float)$state['jackpot'] - $jackpotWin);
        }

        $ret = $lineWin + $jackpotWin;
        $balance = CasinoUserModel::credit($uid, $ret, max(0, $ret - $stake));
        $middle = implode(' ', array_column($grid, 1));
        $detail = $middle . ($wins ? ': ' . implode('; ', array_map(fn($w) => 'lijn ' . ($w['line'] + 1) . ' x' . $w['mult'] . ' = ' . $w['amount'], $wins)) : '');
        $action = $ret > $stake ? 'win' : ($ret === $stake ? 'push' : 'lose');
        CasinoLogModel::write($uid, $action, $ret, $detail . ($jackpotWin ? '; jackpot ' . $jackpotWin : ''), $tableId, 'slot', $round, $balance);
        if ($jackpotWin > 0) {
            CasinoLogModel::write($uid, 'jackpot', 0, 'Jackpot ' . $jackpotWin . ' bij inzet ' . $lineBet . ' per lijn', $tableId, 'slot', $round, $balance);
        }

        $state['last'][$uid] = [
            'spin' => $round,
            'stops' => $stops,
            'wins' => $wins,
            'lineBet' => $lineBet,
            'lines' => $lines,
            'stake' => $stake,
            'win' => $lineWin,
            'jackpot' => $jackpotWin,
        ];
        if ($ret > $stake) {
            array_unshift($state['history'], [
                'round' => $round,
                'name' => CasinoUserModel::publicName($user),
                'middle' => array_column($grid, 1),
                'stake' => $stake,
                'win' => $ret,
                'jackpot' => $jackpotWin,
            ]);
            $state['history'] = array_slice($state['history'], 0, self::HISTORY);
        }
    }

    /** Spins are paid out at once, so there is never anything to give back. */
    public static function refundAll(array $table, array &$state): void
    {
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $players = [];
        foreach ($state['online'] as $id => $row) {
            $players[] = ['name' => $row['n'], 'me' => (int)$id === $uid];
        }
        return [
            'game' => 'slot',
            'round' => $state['round'],
            'remaining' => null,
            'jackpot' => (int)floor((float)$state['jackpot']),
            'history' => $state['history'],
            'mySpin' => $state['last'][$uid] ?? null,
            'players' => $players,
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => (int)$table['min_bet'],
            'maxBet' => (int)$table['max_bet'],
        ];
    }

    /** Static data for the page: the reel strips, the lines and the paytable. */
    public static function clientConfig(): array
    {
        return [
            'reels' => self::REELS,
            'lines' => self::LINES,
            'paytable' => CasinoConfigModel::slotPaytable(),
        ];
    }
}
