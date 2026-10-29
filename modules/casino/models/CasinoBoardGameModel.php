<?php

/**
 * A two-player board game table (chess, xiangqi) played for chips. The rules come from a rules class
 * (CasinoChessRules, CasinoXiangqiRules); this class runs the table: seats, the stake, the clocks and the result.
 *
 * Shared table: two players sit down (seat 1 plays white / red and moves first). When both are there, each pays the
 * stake (the table's min_bet); the winner gets both stakes, a draw gives each their stake back. Every player has a
 * clock (casino_board_minutes, plus casino_board_increment seconds per move); whose time runs out, who resigns,
 * leaves during the game or is away longer than casino_board_away_seconds, loses. After a game the players
 * swap colours.
 * Solo table: the player against the computer (easy, normal or hard) and without a clock. The house pays a win as
 * stake + stake x casino_board_solo_win_<level> percent; a draw gives the stake back.
 *
 * State: phase (waiting / playing / result), round, until, pos (the rules' position), seen (position key => count),
 * moves (the moves as text), turnStart (when the player to move got the turn), draw (the seat that offers a draw),
 * declined (round in which the computer declined a draw), result ([winner seat or 0, reason]), level (solo),
 * online, seats [1, 2 => [uid, name, bot, clock (seconds left at turnStart), away (since when offline), leaving]].
 */
abstract class CasinoBoardGameModel
{
    /** The rules class. */
    abstract protected static function rules(): string;

    /** The game name in the log. */
    abstract protected static function game(): string;

    public const LEVELS = [1 => 'easy', 2 => 'normal', 3 => 'hard'];

    public static function normalize(array $state, array $table): array
    {
        $state = array_merge([
            'phase' => 'waiting',
            'round' => 0,
            'until' => 0,
            'pos' => null,
            'seen' => [],
            'moves' => [],
            'turnStart' => 0,
            'draw' => 0,
            'declined' => 0,
            'result' => null,
            'level' => 2,
            'seats' => [],
            'online' => [],
        ], $state);
        ksort($state['seats']);
        return $state;
    }

    public static function stake(array $table): int
    {
        return max(1, (int)$table['min_bet']);
    }

    private static function clockSeconds(): int
    {
        return 60 * CasinoConfigModel::int('casino_board_minutes', 1, 180);
    }

    private static function seatOf(array $state, int $uid): int
    {
        foreach ($state['seats'] as $no => $seat) {
            if (empty($seat['bot']) && (int)$seat['uid'] === $uid) {
                return (int)$no;
            }
        }
        return 0;
    }

    /** The seat to move: 1 when white / red is to move. */
    private static function turnSeat(array $state): int
    {
        $rules = static::rules();
        return $state['pos'] !== null && $rules::whiteToMove($state['pos']) ? 1 : 2;
    }

    /** Seconds left on the clock of $no right now (the clock of the player to move is running). */
    private static function clockNow(array $state, int $no): ?float
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat === null || $seat['clock'] === null) {
            return null;
        }
        $left = (float)$seat['clock'];
        if ($state['phase'] === 'playing' && self::turnSeat($state) === $no) {
            $left -= microtime(true) - (float)$state['turnStart'];
        }
        return max(0.0, $left);
    }

    private static function log(array $table, array $state, int $no, string $action, int $amount, string $detail, int $balance = -1): void
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat !== null && empty($seat['bot'])) {
            CasinoLogModel::write((int)$seat['uid'], $action, $amount, $detail, (int)$table['casino_table_id'], static::game(), (int)$state['round'], $balance);
        }
    }

    /* ---------- the course of a game ---------- */

    public static function tick(array $table, array &$state): void
    {
        $online = CasinoTableModel::pruneOnline($state);
        $now = time();

        if ($state['phase'] === 'playing') {
            // away too long: lost; time up: lost
            foreach ($state['seats'] as $no => $seat) {
                if (in_array((int)$seat['uid'], $online, true) && empty($seat['leaving'])) {
                    $state['seats'][$no]['away'] = 0;
                    continue;
                }
                if (empty($seat['away'])) {
                    $state['seats'][$no]['away'] = $now;
                }
                if (!empty($seat['leaving']) || $now - (int)$state['seats'][$no]['away'] >= CasinoConfigModel::int('casino_board_away_seconds', 10, 3600)) {
                    self::finish($table, $state, 3 - (int)$no, 'abandon');
                    return;
                }
            }
            $turn = self::turnSeat($state);
            if (self::clockNow($state, $turn) !== null && self::clockNow($state, $turn) <= 0) {
                self::finish($table, $state, 3 - $turn, 'time');
            }
            return;
        }

        if ($state['phase'] === 'result') {
            if ($now < $state['until']) {
                return;
            }
            self::endGame($state, true);
        }

        // between games: who left, went offline or cannot pay the stake stands up
        $stake = self::stake($table);
        foreach ($state['seats'] as $no => $seat) {
            $gone = !empty($seat['leaving']) || !in_array((int)$seat['uid'], $online, true);
            if ($gone || CasinoUserModel::balance((int)$seat['uid']) < $stake) {
                unset($state['seats'][$no]);
                CasinoLogModel::write((int)$seat['uid'], 'leave', 0, ($gone ? 'Van tafel' : 'Te weinig fiches') . ', stoel ' . $no, (int)$table['casino_table_id'], static::game(), (int)$state['round']);
            }
        }
        if (count($state['seats']) < 2) {
            $state['until'] = 0;
        } elseif ($state['until'] === 0) {
            $state['until'] = $now + 3;
        } elseif ($now >= $state['until']) {
            self::startGame($table, $state);
        }
    }

    /** Clears the board; after a shared game the players swap colours. */
    private static function endGame(array &$state, bool $swap): void
    {
        if ($swap && count($state['seats']) === 2) {
            $state['seats'] = [1 => $state['seats'][2], 2 => $state['seats'][1]];
        }
        $state = array_merge($state, ['phase' => 'waiting', 'until' => 0, 'pos' => null, 'seen' => [], 'moves' => [],
            'turnStart' => 0, 'draw' => 0, 'result' => null]);
    }

    private static function startGame(array $table, array &$state): void
    {
        $stake = self::stake($table);
        $tableId = (int)$table['casino_table_id'];
        $state['round']++;
        // both pay the stake; a player who cannot pay any more stands up
        $paid = [];
        foreach ($state['seats'] as $no => $seat) {
            try {
                $balance = CasinoUserModel::debit((int)$seat['uid'], $stake);
                $paid[$no] = $balance;
            } catch (CasinoException $e) {
                foreach ($paid as $n => $b) {
                    CasinoUserModel::refund((int)$state['seats'][$n]['uid'], $stake);
                }
                unset($state['seats'][$no]);
                $state['until'] = 0;
                return;
            }
        }
        foreach ($paid as $no => $balance) {
            self::log($table, $state, $no, 'bet', -$stake, 'Partij #' . $state['round'] . ', ' . ($no === 1 ? 'wit' : 'zwart'), $balance);
        }
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no] = array_merge($seat, ['clock' => (float)self::clockSeconds(), 'away' => 0]);
        }
        self::begin($state);
        CasinoLogModel::write(0, 'deal', 0, 'Partij #' . $state['round'] . ': ' . $state['seats'][1]['name'] . ' - ' . $state['seats'][2]['name'], $tableId, static::game(), $state['round'], 0);
    }

    private static function begin(array &$state): void
    {
        $rules = static::rules();
        $state['pos'] = $rules::start();
        $state['seen'] = [$rules::key($state['pos']) => 1];
        $state['moves'] = [];
        $state['draw'] = 0;
        $state['result'] = null;
        $state['phase'] = 'playing';
        $state['turnStart'] = microtime(true);
        $state['until'] = 0;
    }

    /** Plays the move for seat $no; the game may end with it. */
    private static function move(array $table, array &$state, int $no, array $m): void
    {
        $rules = static::rules();
        $now = microtime(true);
        $seat = $state['seats'][$no];
        if ($seat['clock'] !== null) {
            $left = (float)$seat['clock'] - ($now - (float)$state['turnStart']);
            if ($left <= 0) {
                self::finish($table, $state, 3 - $no, 'time');
                return;
            }
            $state['seats'][$no]['clock'] = $left + CasinoConfigModel::int('casino_board_increment', 0, 600);
        }
        $text = $rules::moveText($m);
        $state['pos'] = $rules::make($state['pos'], $m);
        $key = $rules::key($state['pos']);
        $state['seen'][$key] = ($state['seen'][$key] ?? 0) + 1;
        $state['moves'][] = $text;
        $state['turnStart'] = $now;
        if ($state['draw'] !== 0 && $state['draw'] !== $no) {
            $state['draw'] = 0; // playing on declines the offer of the other
        }
        $out = $rules::outcome($state['pos'], $state['seen']);
        if ($out !== null) {
            self::finish($table, $state, ['white' => 1, 'black' => 2, 'draw' => 0][$out['result']], $out['reason']);
        }
    }

    /**
     * The game is over: $winner is the seat that won, 0 for a draw. Shared: the winner gets both stakes.
     * Solo: the house pays the player by level.
     */
    private static function finish(array $table, array &$state, int $winner, string $reason): void
    {
        $stake = self::stake($table);
        $tableId = (int)$table['casino_table_id'];
        $plies = count($state['moves']);
        $solo = !empty($state['solo']);
        foreach ($state['seats'] as $no => $seat) {
            if (!empty($seat['bot'])) {
                continue;
            }
            $uid = (int)$seat['uid'];
            $detail = ($no === 1 ? 'white' : 'black') . ' ' . $reason . ' ' . $plies;
            if ($winner === 0) {
                $balance = CasinoUserModel::refund($uid, $stake);
                CasinoLogModel::write($uid, 'push', $stake, $detail, $tableId, static::game(), $state['round'], $balance);
            } elseif ($winner === $no) {
                $win = $solo ? intdiv($stake * CasinoConfigModel::int('casino_board_solo_win_' . self::LEVELS[$state['level']], 0, 100), 100) : $stake;
                $balance = CasinoUserModel::credit($uid, $stake + $win, $win);
                CasinoLogModel::write($uid, 'win', $stake + $win, $detail, $tableId, static::game(), $state['round'], $balance);
            } else {
                CasinoLogModel::write($uid, 'lose', 0, $detail, $tableId, static::game(), $state['round']);
            }
        }
        $state['result'] = ['winner' => $winner, 'reason' => $reason];
        $state['phase'] = 'result';
        $state['draw'] = 0;
        $state['until'] = time() + CasinoConfigModel::int('casino_board_result_seconds', 2, 120);
    }

    /* ---------- player actions ---------- */

    public static function act(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        $no = self::seatOf($state, $uid);
        $playing = $state['phase'] === 'playing';

        switch ($action) {
            case 'sit':
                if ($no > 0) {
                    return;
                }
                if ((int)($user['balance'] ?? 0) < self::stake($table)) {
                    throw new CasinoException(CasinoLang::t('err_no_chips'));
                }
                $wanted = (int)($post['seat'] ?? 0);
                if ($wanted < 1 || $wanted > 2 || isset($state['seats'][$wanted])) {
                    $free = array_diff([1, 2], array_keys($state['seats']));
                    $wanted = (int)(reset($free) ?: 0);
                }
                if ($wanted === 0) {
                    throw new CasinoException(CasinoLang::t('err_table_full'));
                }
                $state['seats'][$wanted] = ['uid' => $uid, 'name' => CasinoUserModel::publicName($user), 'bot' => false, 'clock' => null, 'away' => 0, 'leaving' => false];
                ksort($state['seats']);
                CasinoLogModel::write($uid, 'join', 0, 'Stoel ' . $wanted, (int)$table['casino_table_id'], static::game(), (int)$state['round']);
                return;

            case 'leave':
                unset($state['online'][$uid]);
                if ($no === 0) {
                    return;
                }
                if ($playing) {
                    self::finish($table, $state, 3 - $no, 'abandon'); // leaving during a game loses it
                }
                $state['seats'][$no]['leaving'] = true;
                if (!$playing && $state['phase'] !== 'result') {
                    unset($state['seats'][$no]);
                    $state['until'] = 0;
                }
                return;

            case 'move':
                if (!$playing || $no === 0 || self::turnSeat($state) !== $no) {
                    throw new CasinoException(CasinoLang::t('err_not_your_turn'));
                }
                $rules = static::rules();
                $m = $rules::parse($state['pos'], (string)($post['move'] ?? ''));
                if ($m === null) {
                    throw new CasinoException(CasinoLang::t('err_board_move'));
                }
                self::move($table, $state, $no, $m);
                return;

            case 'resign':
                if ($playing && $no > 0) {
                    self::finish($table, $state, 3 - $no, 'resign');
                }
                return;

            case 'draw':
                if (!$playing || $no === 0) {
                    return;
                }
                if ($state['draw'] === 3 - $no) {
                    self::finish($table, $state, 0, 'agreed'); // both want a draw
                    return;
                }
                $state['draw'] = $no;
                return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    /* ---------- solo: against the computer ---------- */

    /** Before every poll and action of a solo player: the computer moves when it is its turn. */
    public static function soloTick(array $table, array &$state, array $user): void
    {
        if ($state['phase'] !== 'playing') {
            return;
        }
        $turn = self::turnSeat($state);
        if (empty($state['seats'][$turn]['bot'])) {
            return;
        }
        $rules = static::rules();
        $m = $rules::bestMove($state['pos'], (int)$state['level']);
        if ($m !== null) {
            self::move($table, $state, $turn, $m);
        }
    }

    public static function soloAct(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        $state['solo'] = true;
        $no = self::seatOf($state, $uid);
        switch ($action) {
            case 'go':
                // a new game: the player picks a colour (1 white / red, 2 black) and a level
                if ($state['phase'] === 'playing') {
                    return;
                }
                $side = (int)($post['seat'] ?? 1) === 2 ? 2 : 1;
                $level = max(1, min(3, (int)($post['level'] ?? 2)));
                $stake = self::stake($table);
                $balance = CasinoUserModel::debit($uid, $stake);
                self::endGame($state, false);
                $state['round']++;
                $state['level'] = $level;
                $state['seats'] = [
                    $side => ['uid' => $uid, 'name' => CasinoUserModel::publicName($user), 'bot' => false, 'clock' => null, 'away' => 0, 'leaving' => false],
                    3 - $side => ['uid' => 0, 'name' => CasinoLang::t('board_bot_' . self::LEVELS[$level]), 'bot' => true, 'clock' => null, 'away' => 0, 'leaving' => false],
                ];
                ksort($state['seats']);
                self::log($table, $state, $side, 'bet', -$stake, 'Partij #' . $state['round'] . ' tegen de computer (' . self::LEVELS[$level] . ')', $balance);
                self::begin($state);
                return;

            case 'draw':
                // the computer agrees to a draw only when it stands clearly worse
                if ($state['phase'] !== 'playing' || $no === 0) {
                    return;
                }
                $rules = static::rules();
                $botScore = $rules::evaluate($state['pos']) * (self::turnSeat($state) === $no ? -1 : 1);
                if ($botScore <= -200) {
                    self::finish($table, $state, 0, 'agreed');
                } else {
                    $state['declined'] = count($state['moves']) + 1;
                }
                return;

            case 'leave':
                if ($no > 0 && $state['phase'] === 'playing') {
                    self::finish($table, $state, 3 - $no, 'abandon');
                }
                return;
        }
        self::act($table, $state, $user, $action, $post);
    }

    /** Admin reset: a game in play is called off; both get their stake back. */
    public static function refundAll(array $table, array &$state): void
    {
        if ($state['phase'] === 'playing') {
            foreach ($state['seats'] as $no => $seat) {
                if (empty($seat['bot'])) {
                    $balance = CasinoUserModel::refund((int)$seat['uid'], self::stake($table));
                    CasinoLogModel::write((int)$seat['uid'], 'refund', self::stake($table), 'Tafel gereset', (int)$table['casino_table_id'], static::game(), (int)$state['round'], $balance);
                }
            }
        }
        $state['seats'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $rules = static::rules();
        $uid = (int)$user['casino_user_id'];
        $myNo = self::seatOf($state, $uid);
        $playing = $state['phase'] === 'playing';
        $turn = $playing ? self::turnSeat($state) : 0;
        $pos = $state['pos'] ?? $rules::start();
        $solo = !empty($state['solo']);

        $seats = [];
        foreach ([1, 2] as $no) {
            $s = $state['seats'][$no] ?? null;
            if ($s === null) {
                $seats[] = ['no' => $no, 'empty' => true];
                continue;
            }
            $clock = self::clockNow($state, $no);
            $seats[] = [
                'no' => $no,
                'empty' => false,
                'name' => $s['name'],
                'me' => $no === $myNo,
                'bot' => !empty($s['bot']),
                'clock' => $clock === null ? null : round($clock, 1),
                'away' => !empty($s['away']) && $playing,
                'turn' => $turn === $no,
            ];
        }
        $myTurn = $myNo > 0 && $turn === $myNo;
        $check = '';
        if ($state['pos'] !== null && $rules::inCheck($pos)) {
            $check = $rules::name(strpos($pos['b'], $rules::whiteToMove($pos) ? 'K' : 'k'));
        }
        $result = $state['result'];

        return [
            'game' => static::game(),
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => $solo ? null : CasinoTableModel::remaining($state),
            'rows' => $rules::grid($pos),
            'seats' => $seats,
            'mySeat' => $myNo,
            'turn' => $turn,
            'legal' => $myTurn ? array_map([$rules, 'moveText'], $rules::legal($pos)) : [],
            'moves' => $state['moves'],
            'last' => $state['moves'] ? end($state['moves']) : '',
            'check' => $check,
            'result' => $result,
            'draw' => (int)$state['draw'],
            'declined' => (int)$state['declined'] === count($state['moves']) + 1 && $playing,
            'level' => (int)$state['level'],
            'stake' => self::stake($table),
            'soloWin' => array_map(fn($l) => CasinoConfigModel::int('casino_board_solo_win_' . $l, 0, 100), self::LEVELS),
            'can' => [
                'sit' => !$solo && $myNo === 0 && count($state['seats']) < 2,
                'move' => $myTurn,
                'resign' => $playing && $myNo > 0,
                'draw' => $playing && $myNo > 0 && (int)$state['draw'] !== $myNo,
                'go' => $solo && !$playing,
                'leave' => $myNo > 0,
            ],
            'watchers' => count($state['online']),
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => self::stake($table),
            'maxBet' => self::stake($table),
        ];
    }
}
