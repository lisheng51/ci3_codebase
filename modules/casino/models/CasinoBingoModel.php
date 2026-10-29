<?php

/**
 * Bingo with 75 balls and 5x5 cards (B 1-15, I 16-30, N 31-45 with a free centre, G 46-60, O 61-75).
 * All players at a table play the same round: buying (the timer starts with the first card) -> drawing
 * (one ball every casino_bingo_draw_seconds, numbers are marked automatically) -> result -> next round.
 * Price per card = the table's min_bet; max_bet = the most cards one player can buy per round (at most MAX_CARDS).
 * Prize pot = card sales x casino_bingo_payout_percent. The first line (row, column or diagonal) wins
 * casino_bingo_line_percent of the pot, the first full card the rest; cards that complete it on the same ball share.
 *
 * State: phase, round, until, cards [id => [uid, name, n (25 numbers row by row, 0 = free centre)]], drawn (in order),
 * spent [uid => chips], won [uid => chips], lineDone, lineWinners, fullWinners (card ids), linePrize, fullPrize,
 * nextId, history (last rounds), online.
 */
class CasinoBingoModel
{
    public const COLUMNS = ['B', 'I', 'N', 'G', 'O'];
    public const MAX_CARDS = 12;
    public const HISTORY = 8;

    public static function normalize(array $state, array $table): array
    {
        return array_merge([
            'phase' => 'buying',
            'round' => 1,
            'until' => 0,
            'cards' => [],
            'drawn' => [],
            'spent' => [],
            'won' => [],
            'lineDone' => false,
            'lineWinners' => [],
            'fullWinners' => [],
            'linePrize' => 0,
            'fullPrize' => 0,
            'nextId' => 1,
            'history' => [],
            'online' => [],
        ], $state);
    }

    public static function letter(int $n): string
    {
        return self::COLUMNS[intdiv($n - 1, 15)] ?? '';
    }

    public static function maxCards(array $table): int
    {
        return max(1, min(self::MAX_CARDS, (int)$table['max_bet']));
    }

    /** A new card: 25 numbers row by row, five per column from that column's range, 0 in the centre. */
    public static function newCard(): array
    {
        $columns = [];
        for ($c = 0; $c < 5; $c++) {
            $pool = range($c * 15 + 1, $c * 15 + 15);
            for ($i = 0; $i < 5; $i++) {
                $j = random_int($i, count($pool) - 1);
                [$pool[$i], $pool[$j]] = [$pool[$j], $pool[$i]];
            }
            $pick = array_slice($pool, 0, 5);
            sort($pick);
            $columns[$c] = $pick;
        }
        $card = [];
        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $card[] = ($r === 2 && $c === 2) ? 0 : $columns[$c][$r];
            }
        }
        return $card;
    }

    /** The 12 lines of a card (5 rows, 5 columns, 2 diagonals) as cell indexes. */
    public static function lines(): array
    {
        $lines = [];
        for ($i = 0; $i < 5; $i++) {
            $lines[] = [$i * 5, $i * 5 + 1, $i * 5 + 2, $i * 5 + 3, $i * 5 + 4];
            $lines[] = [$i, $i + 5, $i + 10, $i + 15, $i + 20];
        }
        $lines[] = [0, 6, 12, 18, 24];
        $lines[] = [4, 8, 12, 16, 20];
        return $lines;
    }

    /** [has a line, is full, numbers still needed for a full card] */
    public static function status(array $numbers, array $drawnSet): array
    {
        $marked = array_map(fn($n) => $n === 0 || isset($drawnSet[$n]), $numbers);
        $line = false;
        foreach (self::lines() as $cells) {
            if (!in_array(false, array_map(fn($i) => $marked[$i], $cells), true)) {
                $line = true;
                break;
            }
        }
        $missing = count(array_filter($marked, fn($m) => !$m));
        return [$line, $missing === 0, $missing];
    }

    public static function tick(array $table, array &$state): void
    {
        CasinoTableModel::pruneOnline($state);
        $now = time();

        if ($state['phase'] === 'buying' && $state['until'] > 0 && $now >= $state['until']) {
            if (empty($state['cards'])) {
                $state['until'] = 0;
            } else {
                $state['phase'] = 'drawing';
                $state['until'] = $now + 2; // a moment for "no more cards"
                CasinoLogModel::write(0, 'deal', 0, count($state['cards']) . ' kaarten, verkoop ' . array_sum($state['spent']), (int)$table['casino_table_id'], 'bingo', $state['round'], 0);
            }
        }

        $draw = CasinoConfigModel::int('casino_bingo_draw_seconds', 1, 30);
        $guard = 0;
        while ($state['phase'] === 'drawing' && $now >= $state['until'] && $guard++ < 80) {
            self::drawBall($table, $state);
            if ($state['phase'] === 'drawing') {
                $state['until'] = max($state['until'] + $draw, $now - 30); // nobody watched for a while: catch up quickly
            }
        }

        if ($state['phase'] === 'result' && $now >= $state['until']) {
            $state = array_merge($state, [
                'phase' => 'buying',
                'round' => $state['round'] + 1,
                'until' => 0,
                'cards' => [],
                'drawn' => [],
                'spent' => [],
                'won' => [],
                'lineDone' => false,
                'lineWinners' => [],
                'fullWinners' => [],
                'linePrize' => 0,
                'fullPrize' => 0,
            ]);
        }
    }

    private static function pot(array $state): int
    {
        return (int)floor(array_sum($state['spent']) * CasinoConfigModel::int('casino_bingo_payout_percent', 0, 100) / 100);
    }

    private static function drawBall(array $table, array &$state): void
    {
        $left = array_values(array_diff(range(1, 75), $state['drawn']));
        if (!$left) {
            return;
        }
        $state['drawn'][] = $left[random_int(0, count($left) - 1)];
        $drawnSet = array_flip($state['drawn']);
        $tableId = (int)$table['casino_table_id'];

        $lines = [];
        $full = [];
        foreach ($state['cards'] as $id => $card) {
            [$line, $isFull] = self::status($card['n'], $drawnSet);
            if ($line) {
                $lines[] = (int)$id;
            }
            if ($isFull) {
                $full[] = (int)$id;
            }
        }

        $pot = self::pot($state);
        if (!$state['lineDone'] && $lines) {
            $prize = (int)floor($pot * CasinoConfigModel::int('casino_bingo_line_percent', 0, 100) / 100);
            $state['linePrize'] = self::pay($table, $state, $lines, $prize, 'Lijn');
            $state['lineDone'] = true;
            $state['lineWinners'] = $lines;
        }
        if ($full) {
            $prize = $pot - (int)floor($pot * CasinoConfigModel::int('casino_bingo_line_percent', 0, 100) / 100);
            $state['fullPrize'] = self::pay($table, $state, $full, $prize, 'Bingo');
            $state['fullWinners'] = $full;
            self::settle($table, $state);
        }
    }

    /** Shares $prize over the winning cards (a player with two winning cards gets two shares). Returns the share per card. */
    private static function pay(array $table, array &$state, array $cardIds, int $prize, string $what): int
    {
        $share = intdiv(max(0, $prize), count($cardIds));
        foreach ($cardIds as $id) {
            $uid = (int)$state['cards'][$id]['uid'];
            if ($share > 0) {
                CasinoUserModel::credit($uid, $share);
            }
            $state['won'][$uid] = ($state['won'][$uid] ?? 0) + $share;
            CasinoLogModel::write($uid, $what === 'Lijn' ? 'line' : 'bingo', 0, $what . ' met kaart ' . $id . ' na ' . count($state['drawn']) . ' ballen: ' . $share, (int)$table['casino_table_id'], 'bingo', $state['round']);
        }
        return $share;
    }

    /** End of the round: one result line per player for the log and the records. */
    private static function settle(array $table, array &$state): void
    {
        $tableId = (int)$table['casino_table_id'];
        foreach ($state['spent'] as $uid => $spent) {
            $uid = (int)$uid;
            $ret = (int)($state['won'][$uid] ?? 0);
            $balance = CasinoUserModel::balance($uid);
            $action = $ret > $spent ? 'win' : ($ret === (int)$spent ? 'push' : 'lose');
            CasinoLogModel::write($uid, $action, $ret, count($state['drawn']) . ' ballen', $tableId, 'bingo', $state['round'], $balance);
            CasinoUserModel::addStats($uid, 0, max(0, $ret - (int)$spent)); // the bets were counted when buying
        }
        $names = fn($ids) => array_values(array_unique(array_map(fn($id) => $state['cards'][$id]['name'], $ids)));
        array_unshift($state['history'], [
            'round' => $state['round'],
            'balls' => count($state['drawn']),
            'line' => ['names' => $names($state['lineWinners']), 'prize' => $state['linePrize']],
            'full' => ['names' => $names($state['fullWinners']), 'prize' => $state['fullPrize']],
        ]);
        $state['history'] = array_slice($state['history'], 0, self::HISTORY);
        $state['phase'] = 'result';
        $state['until'] = time() + CasinoConfigModel::int('casino_bingo_result_seconds', 3, 120);
    }

    private static function myCards(array $state, int $uid): array
    {
        return array_filter($state['cards'], fn($c) => (int)$c['uid'] === $uid);
    }

    public static function act(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        $tableId = (int)$table['casino_table_id'];
        $price = max(1, (int)$table['min_bet']);

        if ($action === 'buy') {
            if ($state['phase'] !== 'buying') {
                throw new CasinoException(CasinoLang::t('err_wait_round'));
            }
            $count = max(1, (int)($post['amount'] ?? 1));
            $have = count(self::myCards($state, $uid));
            $max = self::maxCards($table);
            if ($have + $count > $max) {
                throw new CasinoException(CasinoLang::t('err_bg_max_cards', ['max' => $max]));
            }
            $cost = $price * $count;
            $balance = CasinoUserModel::debit($uid, $cost);
            for ($i = 0; $i < $count; $i++) {
                $state['cards'][$state['nextId']] = ['uid' => $uid, 'name' => CasinoUserModel::publicName($user), 'n' => self::newCard()];
                $state['nextId']++;
            }
            $state['spent'][$uid] = ($state['spent'][$uid] ?? 0) + $cost;
            if ($state['until'] === 0) {
                $state['until'] = time() + CasinoConfigModel::int('casino_bingo_buy_seconds', 5, 600);
            }
            CasinoLogModel::write($uid, 'bet', -$cost, $count . ' kaart(en) x ' . $price, $tableId, 'bingo', $state['round'], $balance);
            return;
        }

        if ($action === 'clear' || $action === 'leave') {
            if ($action === 'leave') {
                unset($state['online'][$uid]);
            }
            $mine = self::myCards($state, $uid);
            if ($state['phase'] !== 'buying' || !$mine) {
                if ($action === 'clear') {
                    throw new CasinoException(CasinoLang::t('err_nothing_to_clear'));
                }
                return; // cards in play stay in play; prizes are paid even after leaving
            }
            $amount = (int)($state['spent'][$uid] ?? 0);
            foreach (array_keys($mine) as $id) {
                unset($state['cards'][$id]);
            }
            unset($state['spent'][$uid]);
            if (!$state['cards']) {
                $state['until'] = 0;
            }
            $balance = CasinoUserModel::refund($uid, $amount);
            CasinoLogModel::write($uid, 'refund', $amount, count($mine) . ' kaart(en)', $tableId, 'bingo', $state['round'], $balance);
            return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    /** Admin reset of a round that is not finished: everybody gets back what they paid for cards. */
    public static function refundAll(array $table, array &$state): void
    {
        if ($state['phase'] === 'result') {
            return;
        }
        foreach ($state['spent'] as $uid => $amount) {
            $balance = CasinoUserModel::refund((int)$uid, (int)$amount);
            CasinoLogModel::write((int)$uid, 'refund', (int)$amount, 'Tafel gereset', (int)$table['casino_table_id'], 'bingo', $state['round'], $balance);
        }
        $state['spent'] = [];
        $state['cards'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $drawnSet = array_flip($state['drawn']);
        $mine = [];
        foreach (self::myCards($state, $uid) as $id => $card) {
            [$line, $full, $missing] = self::status($card['n'], $drawnSet);
            $mine[] = [
                'id' => (int)$id,
                'n' => $card['n'],
                'missing' => $missing,
                'line' => in_array((int)$id, $state['lineWinners'], true),
                'full' => in_array((int)$id, $state['fullWinners'], true),
            ];
        }
        usort($mine, fn($a, $b) => $a['missing'] <=> $b['missing']);

        $counts = [];
        foreach ($state['cards'] as $card) {
            $counts[(int)$card['uid']] = ($counts[(int)$card['uid']] ?? 0) + 1;
        }
        $players = [];
        foreach ($state['online'] as $id => $row) {
            $players[(int)$id] = ['name' => $row['n'], 'cards' => $counts[(int)$id] ?? 0, 'won' => (int)($state['won'][$id] ?? 0), 'me' => (int)$id === $uid];
        }
        $winners = fn($ids) => array_values(array_unique(array_map(fn($id) => $state['cards'][$id]['name'] ?? '?', $ids)));
        $pot = self::pot($state);
        $linePrize = (int)floor($pot * CasinoConfigModel::int('casino_bingo_line_percent', 0, 100) / 100);
        $draw = CasinoConfigModel::int('casino_bingo_draw_seconds', 1, 30);

        return [
            'game' => 'bingo',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => $state['phase'] === 'drawing' ? null : CasinoTableModel::remaining($state),
            'drawn' => $state['drawn'],
            'pot' => $pot,
            'linePrize' => $state['lineDone'] ? $state['linePrize'] : $linePrize,
            'fullPrize' => $state['phase'] === 'result' ? $state['fullPrize'] : $pot - $linePrize,
            'lineDone' => $state['lineDone'],
            'lineWinners' => $winners($state['lineWinners']),
            'fullWinners' => $winners($state['fullWinners']),
            'myCards' => $mine,
            'myBets' => (object)(in_array($state['phase'], ['buying', 'drawing'], true) && $mine ? ['cards' => count($mine)] : []),
            'myWon' => (int)($state['won'][$uid] ?? 0),
            'cardPrice' => max(1, (int)$table['min_bet']),
            'maxCards' => self::maxCards($table),
            'cardsSold' => count($state['cards']),
            'drawSeconds' => $draw,
            'players' => array_values($players),
            'history' => $state['history'],
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => max(1, (int)$table['min_bet']),
            'maxBet' => self::maxCards($table),
        ];
    }
}
