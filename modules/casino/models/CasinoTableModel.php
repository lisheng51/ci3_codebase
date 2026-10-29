<?php

/**
 * A shared game table. Its running round lives in state_json and is only read and written while a
 * MySQL named lock (GET_LOCK) for that table is held, so many players can act at the same time.
 * There is no cron: every poll first lets the game move on when a timer has run out (tick).
 */
class CasinoTableModel
{
    use BasicModel;

    public const GAMES = [
        'dice' => 'Dobbelstenen (Sic Bo)',
        'blackjack' => 'Blackjack (21)',
        'roulette' => 'Roulette',
        'slot' => 'Fruitautomaat',
        'holdem' => "Texas Hold'em Poker",
        'bingo' => 'Bingo',
        'doudizhu' => 'Dou Dizhu (斗地主)',
        'chess' => 'Schaken',
        'xiangqi' => 'Chinees schaak (象棋)',
    ];

    public const MAX_SEATS = 9;

    /** Games where players take a seat (the table's max_seats counts). */
    public const SEATED_GAMES = ['blackjack', 'holdem'];

    public static function __constructStatic()
    {
        self::$table = "casino_table";
        self::$primaryKey = "casino_table_id";
        self::$sqlOrderBy = ["order_num" => 'asc', "casino_table_id" => 'asc'];
        self::$selectOrderBy = [
            'casino_table_id#desc' => 'ID (aflopend)',
            'name#asc' => 'Naam (oplopend)',
            'order_num#asc' => 'Volgorde (oplopend)',
        ];
    }

    /** The class with the rules of a game. */
    public static function engine(string $game): string
    {
        return [
            'blackjack' => CasinoBlackjackModel::class,
            'roulette' => CasinoRouletteModel::class,
            'slot' => CasinoSlotModel::class,
            'holdem' => CasinoHoldemModel::class,
            'bingo' => CasinoBingoModel::class,
            'doudizhu' => CasinoDoudizhuModel::class,
            'chess' => CasinoChessModel::class,
            'xiangqi' => CasinoXiangqiModel::class,
        ][$game] ?? CasinoDiceModel::class;
    }

    public static function gameName(string $game): string
    {
        $text = CasinoLang::t('game_' . $game);
        return $text !== 'game_' . $game ? $text : (self::GAMES[$game] ?? $game);
    }

    public static function fetch(int $id): array
    {
        $rs = CIDb()->query('SELECT * FROM ' . CIDb()->dbprefix(self::$table) . ' WHERE casino_table_id = ?', [$id])->row_array();
        return is_array($rs) ? $rs : [];
    }

    /**
     * Runs $fn(array $table, array &$state) while holding the lock of the table and saves the state
     * when it changed. The game is ticked first, so timers that ran out are handled.
     */
    public static function locked(int $id, callable $fn)
    {
        $lockName = substr(CIDb()->database . '.casino_table_' . $id, -64);
        $got = CIDb()->query('SELECT GET_LOCK(?, 5) AS l', [$lockName])->row_array();
        if ((int)($got['l'] ?? 0) !== 1) {
            throw new CasinoException(CasinoLang::t('err_busy'));
        }

        try {
            $table = self::fetch($id);
            if (empty($table)) {
                throw new CasinoException(CasinoLang::t('err_table_not_found'));
            }
            $engine = self::engine($table['game']);
            $before = (string)$table['state_json'];
            $state = json_decode($before, true);
            $state = $engine::normalize(is_array($state) ? $state : [], $table);
            try {
                // the tick may already have paid out chips, so the state is saved even when $fn refuses the action
                if (self::isSolo($table)) {
                    self::soloTick($state);
                } else {
                    $engine::tick($table, $state);
                }
                return $fn($table, $state);
            } finally {
                $after = json_encode($state, JSON_UNESCAPED_UNICODE);
                if ($after !== $before) {
                    CIDb()->query(
                        'UPDATE ' . CIDb()->dbprefix(self::$table) . ' SET state_json = ?, round_no = ? WHERE casino_table_id = ?',
                        [$after, (int)($state['round'] ?? 0), $id]
                    );
                }
            }
        } finally {
            CIDb()->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    /** What the player sees of the table; also marks the player as online at this table. */
    public static function poll(int $id, array $user): array
    {
        return self::locked($id, function (array $table, array &$state) use ($user) {
            self::markOnline($state, $user);
            if (self::isSolo($table)) {
                $engine = self::engine($table['game']);
                if (method_exists($engine, 'soloTick')) {
                    // dou dizhu: the computer players move while the player watches, so the round is saved
                    $own = self::soloState($table, $state, $user);
                    $own['online'] = [];
                    $state['solo'][(int)$user['casino_user_id']] = $own;
                }
                return self::soloView($table, $state, $user);
            }
            return self::viewFor($table, $state, $user);
        });
    }

    /** Solo table: every player has their own round in state['solo'][uid]. */
    private static function soloState(array $table, array $state, array $user, bool $tick = true): array
    {
        $engine = self::engine($table['game']);
        $own = $engine::normalize($state['solo'][(int)$user['casino_user_id']] ?? [], $table);
        if (method_exists($engine, 'soloPrepare')) {
            $engine::soloPrepare($own, $user); // blackjack: the player's own seat
        }
        if ($tick && method_exists($engine, 'soloTick')) {
            $engine::soloTick($table, $own, $user); // dou dizhu: the computer players' moves
        }
        return $own;
    }

    private static function soloView(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $own = self::soloState($table, $state, $user, false); // already moved on and saved by poll or act
        $own['online'] = isset($state['online'][$uid]) ? [$uid => $state['online'][$uid]] : [];
        $engine = self::engine($table['game']);
        $table['max_seats'] = 1; // blackjack: only the player's own seat
        $view = $engine::view($table, $own, $user);
        $view['solo'] = true;
        $view['remaining'] = null;
        $view['watchers'] = count($state['online'] ?? []);
        $view['myRecent'] = CasinoLogModel::recentRounds($uid, (int)$table['casino_table_id'], (int)$own['round']);
        return $view;
    }

    /** Solo table: forgets the rounds of players who went away without open bets. */
    private static function soloTick(array &$state): void
    {
        $online = self::pruneOnline($state);
        foreach ($state['solo'] ?? [] as $uid => $own) {
            // open: bets not played yet, or a blackjack hand in play
            $phase = $own['phase'] ?? '';
            $bet = !empty($own['bets']) || array_sum(array_column($own['seats'] ?? [], 'bet')) > 0;
            // dou dizhu: chips on the table (the stack) stay until the player takes them back
            $stack = array_sum(array_map(fn($s) => empty($s['bot']) ? (int)($s['stack'] ?? 0) : 0, $own['seats'] ?? []));
            $open = $phase === 'playing' || ($phase === 'betting' && $bet) || $stack > 0;
            if (!$open && !in_array((int)$uid, $online, true)) {
                unset($state['solo'][$uid]);
            }
        }
    }

    /** A player action (bet, hit, ...). Returns the new view. */
    public static function act(int $id, array $user, string $action, array $post): array
    {
        return self::locked($id, function (array $table, array &$state) use ($user, $action, $post) {
            if (empty($table['is_active']) && !in_array($action, ['leave', 'clear'], true)) {
                throw new CasinoException(CasinoLang::t('err_table_closed'));
            }
            self::markOnline($state, $user);
            $engine = self::engine($table['game']);
            if (self::isSolo($table)) {
                $uid = (int)$user['casino_user_id'];
                $own = self::soloState($table, $state, $user);
                try {
                    $engine::soloAct($table, $own, $user, $action, $post);
                } finally {
                    $own['online'] = []; // kept once, in the table state
                    $state['solo'][$uid] = $own;
                }
                return self::soloView($table, $state, CasinoUserModel::find($uid));
            }
            $engine::act($table, $state, $user, $action, $post);
            return self::viewFor($table, $state, CasinoUserModel::find((int)$user['casino_user_id']));
        });
    }

    /** The table as the player sees it, plus the player's own last results at this table. */
    private static function viewFor(array $table, array $state, array $user): array
    {
        $engine = self::engine($table['game']);
        $view = $engine::view($table, $state, $user);
        $view['myRecent'] = CasinoLogModel::recentRounds((int)$user['casino_user_id'], (int)$table['casino_table_id'], (int)($state['round'] ?? 0));
        return $view;
    }

    /** Admin: stops the running round, gives every open bet back and empties the table. */
    public static function reset(int $id): void
    {
        self::locked($id, function (array $table, array &$state) {
            $engine = self::engine($table['game']);
            $engine::refundAll($table, $state);
            // solo rounds of the players (also when the table was switched from solo to shared)
            foreach ($state['solo'] ?? [] as $uid => $own) {
                $own = $engine::normalize($own, $table);
                $engine::refundAll($table, $own);
            }
            $round = (int)($state['round'] ?? 0);
            $state = $engine::normalize(['round' => $round + 1], $table);
        });
    }

    public static function markOnline(array &$state, array $user): void
    {
        $uid = (int)$user['casino_user_id'];
        $seen = (int)($state['online'][$uid]['t'] ?? 0);
        if ($seen < time() - 5) {
            $state['online'][$uid] = ['n' => CasinoUserModel::publicName($user), 't' => time()];
        }
    }

    /** Removes players that did not poll for a while from the online list; returns the ids still online. */
    public static function pruneOnline(array &$state): array
    {
        $limit = time() - CasinoConfigModel::int('casino_idle_seconds', 15, 3600);
        foreach ($state['online'] ?? [] as $uid => $row) {
            if ((int)($row['t'] ?? 0) < $limit) {
                unset($state['online'][$uid]);
            }
        }
        return array_map('intval', array_keys($state['online'] ?? []));
    }

    /** Number of players online at a table, for the lobby (read without lock). */
    public static function onlineCount(array $table): int
    {
        $state = json_decode((string)$table['state_json'], true);
        $limit = time() - CasinoConfigModel::int('casino_idle_seconds', 15, 3600);
        $count = 0;
        foreach (($state['online'] ?? []) as $row) {
            if ((int)($row['t'] ?? 0) >= $limit) {
                $count++;
            }
        }
        return $count;
    }

    public static function remaining(array $state): ?int
    {
        $until = (int)($state['until'] ?? 0);
        return $until > 0 ? max(0, $until - time()) : null;
    }

    /** Games that can be played solo: the player starts the roll / spin, nobody waits for a timer. */
    public const SOLO_GAMES = ['dice', 'roulette', 'blackjack', 'doudizhu', 'chess', 'xiangqi'];

    public static function isSolo(array $table): bool
    {
        return !empty($table['solo']) && in_array($table['game'], self::SOLO_GAMES, true);
    }

    /**
     * Columns added after the first release; the framework has no migrations, so they are added here
     * (called from the admin table pages). A missing column simply reads as 0.
     */
    public static function ensureSchema(): void
    {
        if (!CIDb()->field_exists('solo', self::$table)) {
            CIDb()->query('ALTER TABLE ' . CIDb()->dbprefix(self::$table) . ' ADD `solo` tinyint(1) NOT NULL DEFAULT 0 AFTER `max_seats`');
        }
    }

    public static function postData(): array
    {
        $data['name'] = trim((string)CIInput()->post('name'));
        AjaxckModel::value('name', $data['name']);
        $game = (string)CIInput()->post('game');
        $data['game'] = isset(self::GAMES[$game]) ? $game : 'dice';
        $data['min_bet'] = max(1, (int)CIInput()->post('min_bet'));
        // bingo: max_bet is the number of cards per player, not an amount
        // chess, xiangqi: only the stake (min_bet) counts
        $data['max_bet'] = in_array($data['game'], ['chess', 'xiangqi'], true) ? $data['min_bet'] : ($data['game'] === 'bingo'
            ? max(1, (int)CIInput()->post('max_bet'))
            : max($data['min_bet'], (int)CIInput()->post('max_bet')));
        // poker needs at least two seats
        $data['max_seats'] = max($data['game'] === 'holdem' ? 2 : 1, min(self::MAX_SEATS, (int)CIInput()->post('max_seats')));
        $data['solo'] = in_array($data['game'], self::SOLO_GAMES, true) && (int)CIInput()->post('solo') > 0 ? 1 : 0;
        $data['is_active'] = (int)(CIInput()->post('is_active') ?? 1) > 0 ? 1 : 0;
        $data['order_num'] = max(0, (int)CIInput()->post('order_num'));
        return $data;
    }
}
