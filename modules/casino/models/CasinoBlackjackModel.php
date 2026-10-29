<?php

/**
 * Blackjack against the bank for up to MAX_SEATS players at one table, one hand per seat.
 * Round: betting (the timer starts with the first bet) -> playing (the seats take turns, each with a time limit;
 * when it runs out the hand stands) -> the bank draws and everybody is paid out -> result -> next round.
 * Hit, stand and double down; no split and no insurance. The bank checks for blackjack right after the deal.
 *
 * State: phase, round, until, turn (seat number), shoe, dealer (cards), online,
 * seats [no => [uid, name, bet, cards, done, doubled, leaving, outcome, ret]].
 * A card is rank + suit, e.g. "AS", "10H", "QD".
 */
class CasinoBlackjackModel
{
    private const SUITS = ['S', 'H', 'D', 'C'];
    private const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public static function normalize(array $state, array $table): array
    {
        $state = array_merge([
            'phase' => 'betting',
            'round' => 1,
            'until' => 0,
            'turn' => 0,
            'shoe' => [],
            'dealer' => [],
            'seats' => [],
            'online' => [],
        ], $state);
        ksort($state['seats']);
        return $state;
    }

    public static function tick(array $table, array &$state): void
    {
        $online = CasinoTableModel::pruneOnline($state);
        $now = time();

        if ($state['phase'] === 'betting') {
            // players that closed the page lose their seat (their bet goes back)
            foreach ($state['seats'] as $no => $seat) {
                if (!in_array((int)$seat['uid'], $online, true)) {
                    self::removeSeat($table, $state, (int)$no, 'Geen verbinding meer');
                }
            }
            if (!self::hasBets($state)) {
                $state['until'] = 0;
            } elseif ($state['until'] > 0 && $now >= $state['until']) {
                self::deal($table, $state);
            }
        } elseif ($state['phase'] === 'playing' && $now >= $state['until']) {
            $turn = $state['turn'];
            if (isset($state['seats'][$turn])) {
                $state['seats'][$turn]['done'] = true;
                CasinoLogModel::write((int)$state['seats'][$turn]['uid'], 'timeout', 0, 'Automatisch gepast: ' . self::handText($state['seats'][$turn]['cards']), (int)$table['casino_table_id'], 'blackjack', $state['round']);
            }
            self::advance($table, $state);
        } elseif ($state['phase'] === 'result' && $now >= $state['until']) {
            $state['phase'] = 'betting';
            $state['round']++;
            $state['until'] = 0;
            $state['turn'] = 0;
            $state['dealer'] = [];
            foreach ($state['seats'] as $no => $seat) {
                if (!empty($seat['leaving']) || !in_array((int)$seat['uid'], $online, true)) {
                    unset($state['seats'][$no]);
                    CasinoLogModel::write((int)$seat['uid'], 'leave', 0, (!empty($seat['leaving']) ? 'Van tafel' : 'Geen verbinding meer') . ', stoel ' . $no, (int)$table['casino_table_id'], 'blackjack', $state['round']);
                    continue;
                }
                $state['seats'][$no] = self::emptySeat((int)$seat['uid'], $seat['name']);
            }
        }
    }

    /** Solo table: $state is the player's own table, with only seat 1 (theirs) against the dealer. */
    public static function soloPrepare(array &$state, array $user): void
    {
        $uid = (int)$user['casino_user_id'];
        if (!isset($state['seats'][1]) || (int)$state['seats'][1]['uid'] !== $uid || count($state['seats']) !== 1) {
            $state['seats'] = [1 => self::emptySeat($uid, CasinoUserModel::publicName($user))];
        }
    }

    /**
     * Solo table: 'go' deals at once; hit, stand and double have no time limit (nobody waits).
     * A bet after the result starts the next hand. A hand in play stays when the player leaves.
     */
    public static function soloAct(array $table, array &$state, array $user, string $action, array $post): void
    {
        self::soloPrepare($state, $user);
        if ($action === 'go') {
            if ($state['phase'] !== 'betting' || $state['seats'][1]['bet'] <= 0) {
                throw new CasinoException(CasinoLang::t('err_solo_no_bets'));
            }
            self::deal($table, $state);
        } elseif ($action === 'leave') {
            if ($state['phase'] === 'betting' && $state['seats'][1]['bet'] > 0) {
                self::act($table, $state, $user, 'clear', []);
            }
        } elseif ($action !== 'sit') {
            if ($action === 'bet' && $state['phase'] === 'result') {
                $state['phase'] = 'betting';
                $state['round']++;
                $state['turn'] = 0;
                $state['dealer'] = [];
                $state['seats'] = [1 => self::emptySeat((int)$user['casino_user_id'], CasinoUserModel::publicName($user))];
            }
            self::act($table, $state, $user, $action, $post);
        }
        $state['until'] = 0; // no timers when playing solo
    }

    private static function emptySeat(int $uid, string $name): array
    {
        return ['uid' => $uid, 'name' => $name, 'bet' => 0, 'cards' => [], 'done' => false, 'doubled' => false, 'leaving' => false, 'outcome' => '', 'ret' => null];
    }

    private static function seatOf(array $state, int $uid): int
    {
        foreach ($state['seats'] as $no => $seat) {
            if ((int)$seat['uid'] === $uid) {
                return (int)$no;
            }
        }
        return 0;
    }

    private static function hasBets(array $state): bool
    {
        foreach ($state['seats'] as $seat) {
            if ($seat['bet'] > 0) {
                return true;
            }
        }
        return false;
    }

    public static function act(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        $tableId = (int)$table['casino_table_id'];
        $no = self::seatOf($state, $uid);

        switch ($action) {
            case 'sit':
                if ($no > 0) {
                    return;
                }
                $wanted = (int)($post['seat'] ?? 0);
                $max = max(1, min(CasinoTableModel::MAX_SEATS, (int)$table['max_seats']));
                if ($wanted < 1 || $wanted > $max || isset($state['seats'][$wanted])) {
                    $wanted = 0;
                    for ($i = 1; $i <= $max; $i++) {
                        if (!isset($state['seats'][$i])) {
                            $wanted = $i;
                            break;
                        }
                    }
                }
                if ($wanted === 0) {
                    throw new CasinoException(CasinoLang::t('err_table_full'));
                }
                $state['seats'][$wanted] = self::emptySeat($uid, CasinoUserModel::publicName($user));
                ksort($state['seats']);
                CasinoLogModel::write($uid, 'join', 0, 'Stoel ' . $wanted, $tableId, 'blackjack', $state['round']);
                return;

            case 'leave':
                unset($state['online'][$uid]);
                if ($no === 0) {
                    return;
                }
                if ($state['phase'] === 'playing' && $state['seats'][$no]['bet'] > 0) {
                    // the hand in play stands; the seat is freed after the round
                    $state['seats'][$no]['leaving'] = true;
                    if (!$state['seats'][$no]['done']) {
                        $state['seats'][$no]['done'] = true;
                        CasinoLogModel::write($uid, 'stand', 0, 'Van tafel: ' . self::handText($state['seats'][$no]['cards']), $tableId, 'blackjack', $state['round']);
                        if ($state['turn'] === $no) {
                            self::advance($table, $state);
                        }
                    }
                    return;
                }
                self::removeSeat($table, $state, $no, 'Van tafel');
                return;

            case 'bet':
                if ($no === 0) {
                    throw new CasinoException(CasinoLang::t('err_sit_first'));
                }
                if ($state['phase'] !== 'betting') {
                    throw new CasinoException(CasinoLang::t('err_wait_round'));
                }
                $amount = (int)($post['amount'] ?? 0);
                $current = (int)$state['seats'][$no]['bet'];
                if ($amount <= 0 || $current + $amount < (int)$table['min_bet']) {
                    throw new CasinoException(CasinoLang::t('err_min_bet', ['amount' => (int)$table['min_bet']]));
                }
                if ($current + $amount > (int)$table['max_bet']) {
                    throw new CasinoException(CasinoLang::t('err_max_bet', ['amount' => (int)$table['max_bet']]));
                }
                $balance = CasinoUserModel::debit($uid, $amount);
                $state['seats'][$no]['bet'] = $current + $amount;
                $betSeconds = CasinoConfigModel::int('casino_bj_bet_seconds', 3, 600);
                if ($state['until'] === 0) {
                    $state['until'] = time() + $betSeconds;
                }
                $allIn = true;
                foreach ($state['seats'] as $seat) {
                    $allIn = $allIn && $seat['bet'] > 0;
                }
                if ($allIn) {
                    // everybody at the table has bet: no need to wait for the full timer
                    $state['until'] = min($state['until'], time() + 3);
                }
                CasinoLogModel::write($uid, 'bet', -$amount, 'Stoel ' . $no, $tableId, 'blackjack', $state['round'], $balance);
                return;

            case 'clear':
                if ($no === 0 || $state['phase'] !== 'betting' || $state['seats'][$no]['bet'] <= 0) {
                    throw new CasinoException(CasinoLang::t('err_nothing_to_clear'));
                }
                $amount = (int)$state['seats'][$no]['bet'];
                $state['seats'][$no]['bet'] = 0;
                $balance = CasinoUserModel::refund($uid, $amount);
                CasinoLogModel::write($uid, 'refund', $amount, 'Stoel ' . $no, $tableId, 'blackjack', $state['round'], $balance);
                return;

            case 'hit':
            case 'stand':
            case 'double':
                if ($state['phase'] !== 'playing' || $no === 0 || $state['turn'] !== $no) {
                    throw new CasinoException(CasinoLang::t('err_not_your_turn'));
                }
                $seat = &$state['seats'][$no];
                if ($action === 'double') {
                    if (!CasinoConfigModel::bool('casino_bj_allow_double') || count($seat['cards']) !== 2) {
                        throw new CasinoException(CasinoLang::t('err_double_first_two'));
                    }
                    $balance = CasinoUserModel::debit($uid, (int)$seat['bet']);
                    CasinoLogModel::write($uid, 'double', -(int)$seat['bet'], 'Stoel ' . $no, $tableId, 'blackjack', $state['round'], $balance);
                    $seat['bet'] *= 2;
                    $seat['doubled'] = true;
                    $seat['cards'][] = self::draw($state);
                    $seat['done'] = true;
                } elseif ($action === 'hit') {
                    $seat['cards'][] = self::draw($state);
                    $value = self::value($seat['cards'])[0];
                    $seat['done'] = $value >= 21;
                    CasinoLogModel::write($uid, 'hit', 0, self::handText($seat['cards']) . ($value > 21 ? ' kapot' : ''), $tableId, 'blackjack', $state['round']);
                } else {
                    $seat['done'] = true;
                    CasinoLogModel::write($uid, 'stand', 0, self::handText($seat['cards']), $tableId, 'blackjack', $state['round']);
                }
                $done = $seat['done'];
                unset($seat);
                if ($done) {
                    self::advance($table, $state);
                } else {
                    $state['until'] = time() + CasinoConfigModel::int('casino_bj_turn_seconds', 5, 600);
                }
                return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    private static function removeSeat(array $table, array &$state, int $no, string $reason): void
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat === null) {
            return;
        }
        unset($state['seats'][$no]);
        $uid = (int)$seat['uid'];
        $tableId = (int)$table['casino_table_id'];
        if ($state['phase'] === 'betting' && $seat['bet'] > 0) {
            $balance = CasinoUserModel::refund($uid, (int)$seat['bet']);
            CasinoLogModel::write($uid, 'refund', (int)$seat['bet'], $reason, $tableId, 'blackjack', $state['round'], $balance);
        }
        CasinoLogModel::write($uid, 'leave', 0, $reason . ', stoel ' . $no, $tableId, 'blackjack', $state['round']);
    }

    private static function newShoe(int $decks): array
    {
        $shoe = [];
        for ($d = 0; $d < $decks; $d++) {
            foreach (self::SUITS as $suit) {
                foreach (self::RANKS as $rank) {
                    $shoe[] = $rank . $suit;
                }
            }
        }
        for ($i = count($shoe) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$shoe[$i], $shoe[$j]] = [$shoe[$j], $shoe[$i]];
        }
        return $shoe;
    }

    private static function draw(array &$state): string
    {
        if (empty($state['shoe'])) {
            $state['shoe'] = self::newShoe(CasinoConfigModel::int('casino_bj_decks', 1, 8));
        }
        return array_pop($state['shoe']);
    }

    /** [best value, soft] where soft means an ace still counts as 11. */
    public static function value(array $cards): array
    {
        $total = 0;
        $aces = 0;
        foreach ($cards as $card) {
            $rank = substr($card, 0, -1);
            if ($rank === 'A') {
                $aces++;
                $total += 11;
            } elseif (in_array($rank, ['J', 'Q', 'K'], true)) {
                $total += 10;
            } else {
                $total += (int)$rank;
            }
        }
        while ($total > 21 && $aces > 0) {
            $total -= 10;
            $aces--;
        }
        return [$total, $aces > 0];
    }

    public static function isBlackjack(array $cards): bool
    {
        return count($cards) === 2 && self::value($cards)[0] === 21;
    }

    private static function handText(array $cards): string
    {
        return implode(' ', $cards) . ' (' . self::value($cards)[0] . ')';
    }

    private static function deal(array $table, array &$state): void
    {
        $decks = CasinoConfigModel::int('casino_bj_decks', 1, 8);
        if (count($state['shoe']) < $decks * 52 / 4) {
            $state['shoe'] = self::newShoe($decks);
        }

        $state['dealer'] = [];
        $bettors = [];
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no]['cards'] = [];
            $state['seats'][$no]['done'] = $seat['bet'] <= 0;
            if ($seat['bet'] > 0) {
                $bettors[] = $no;
            }
        }
        for ($round = 0; $round < 2; $round++) {
            foreach ($bettors as $no) {
                $state['seats'][$no]['cards'][] = self::draw($state);
            }
            $state['dealer'][] = self::draw($state);
        }

        $names = [];
        foreach ($bettors as $no) {
            if (self::isBlackjack($state['seats'][$no]['cards'])) {
                $state['seats'][$no]['done'] = true;
            }
            $names[] = $state['seats'][$no]['name'] . ' ' . $state['seats'][$no]['bet'];
        }
        CasinoLogModel::write(0, 'deal', 0, 'Spelers: ' . implode(', ', $names), (int)$table['casino_table_id'], 'blackjack', $state['round'], 0);

        $state['phase'] = 'playing';
        $state['turn'] = 0;
        if (self::isBlackjack($state['dealer'])) {
            self::finish($table, $state);
            return;
        }
        self::advance($table, $state);
    }

    /** Gives the turn to the next seat that still has to play, or lets the bank play when everybody is done. */
    private static function advance(array $table, array &$state): void
    {
        foreach ($state['seats'] as $no => $seat) {
            if ((int)$no > $state['turn'] && $seat['bet'] > 0 && !$seat['done']) {
                $state['turn'] = (int)$no;
                $state['until'] = time() + CasinoConfigModel::int('casino_bj_turn_seconds', 5, 600);
                return;
            }
        }
        self::finish($table, $state);
    }

    private static function finish(array $table, array &$state): void
    {
        $dealerBj = self::isBlackjack($state['dealer']);
        $mustDraw = false;
        foreach ($state['seats'] as $seat) {
            if ($seat['bet'] > 0 && self::value($seat['cards'])[0] <= 21 && !self::isBlackjack($seat['cards'])) {
                $mustDraw = true;
            }
        }
        $hitsSoft17 = CasinoConfigModel::bool('casino_bj_dealer_hits_soft17');
        while ($mustDraw && !$dealerBj) {
            [$value, $soft] = self::value($state['dealer']);
            if ($value < 17 || ($value === 17 && $soft && $hitsSoft17)) {
                $state['dealer'][] = self::draw($state);
                continue;
            }
            break;
        }

        $dealerValue = self::value($state['dealer'])[0];
        $payout = CasinoConfigModel::float('casino_bj_payout', 0, 10);
        $tableId = (int)$table['casino_table_id'];
        foreach ($state['seats'] as $no => $seat) {
            if ($seat['bet'] <= 0) {
                continue;
            }
            $bet = (int)$seat['bet'];
            $value = self::value($seat['cards'])[0];
            $playerBj = self::isBlackjack($seat['cards']);
            if ($value > 21) {
                [$outcome, $ret] = ['lose', 0];
            } elseif ($playerBj && !$dealerBj) {
                [$outcome, $ret] = ['blackjack', $bet + (int)floor($bet * $payout)];
            } elseif ($dealerBj && !$playerBj) {
                [$outcome, $ret] = ['lose', 0];
            } elseif ($playerBj && $dealerBj) {
                [$outcome, $ret] = ['push', $bet];
            } elseif ($dealerValue > 21 || $value > $dealerValue) {
                [$outcome, $ret] = ['win', $bet * 2];
            } elseif ($value === $dealerValue) {
                [$outcome, $ret] = ['push', $bet];
            } else {
                [$outcome, $ret] = ['lose', 0];
            }

            $balance = CasinoUserModel::credit((int)$seat['uid'], $ret, max(0, $ret - $bet));
            $state['seats'][$no]['outcome'] = $outcome;
            $state['seats'][$no]['ret'] = $ret;
            $state['seats'][$no]['done'] = true;
            CasinoLogModel::write(
                (int)$seat['uid'],
                $outcome,
                $ret,
                'Inzet ' . $bet . ($seat['doubled'] ? ' (verdubbeld)' : '') . ', speler ' . self::handText($seat['cards']) . ' - bank ' . self::handText($state['dealer']),
                $tableId,
                'blackjack',
                $state['round'],
                $balance
            );
        }

        $state['phase'] = 'result';
        $state['turn'] = 0;
        $state['until'] = time() + CasinoConfigModel::int('casino_bj_result_seconds', 2, 120);
    }

    public static function refundAll(array $table, array &$state): void
    {
        if ($state['phase'] === 'result') {
            return; // already paid out
        }
        foreach ($state['seats'] as $seat) {
            if ($seat['bet'] > 0) {
                $balance = CasinoUserModel::refund((int)$seat['uid'], (int)$seat['bet']);
                CasinoLogModel::write((int)$seat['uid'], 'refund', (int)$seat['bet'], 'Tafel gereset', (int)$table['casino_table_id'], 'blackjack', $state['round'], $balance);
            }
        }
        $state['seats'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $myNo = self::seatOf($state, $uid);
        $max = max(1, min(CasinoTableModel::MAX_SEATS, (int)$table['max_seats']));

        $seats = [];
        for ($no = 1; $no <= $max; $no++) {
            $seat = $state['seats'][$no] ?? null;
            if ($seat === null) {
                $seats[] = ['no' => $no, 'empty' => true];
                continue;
            }
            $seats[] = [
                'no' => $no,
                'empty' => false,
                'name' => $seat['name'],
                'me' => $no === $myNo,
                'bet' => (int)$seat['bet'],
                'cards' => $seat['cards'],
                'value' => $seat['cards'] ? self::value($seat['cards'])[0] : 0,
                'blackjack' => self::isBlackjack($seat['cards']),
                'doubled' => (bool)$seat['doubled'],
                'outcome' => $seat['outcome'],
                'ret' => $seat['ret'],
                'turn' => $state['phase'] === 'playing' && $state['turn'] === $no,
            ];
        }

        $dealer = $state['dealer'];
        $dealerValue = $dealer ? self::value($dealer)[0] : 0;
        if ($state['phase'] === 'playing' && count($dealer) >= 2) {
            $dealer = [$dealer[0], '??'];
            $dealerValue = self::value([$state['dealer'][0]])[0];
        }

        $mySeat = $myNo > 0 ? $state['seats'][$myNo] : null;
        $myTurn = $mySeat && $state['phase'] === 'playing' && $state['turn'] === $myNo;
        $balance = (int)($user['balance'] ?? 0);
        $hasFree = count($state['seats']) < $max;

        return [
            'game' => 'blackjack',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => CasinoTableModel::remaining($state),
            'seats' => $seats,
            'dealer' => $dealer,
            'dealerValue' => $dealerValue,
            'mySeat' => $myNo,
            'myBet' => $mySeat ? (int)$mySeat['bet'] : 0,
            'can' => [
                'sit' => $myNo === 0 && $hasFree,
                'bet' => $myNo > 0 && $state['phase'] === 'betting',
                'clear' => $myNo > 0 && $state['phase'] === 'betting' && $mySeat['bet'] > 0,
                'hit' => $myTurn,
                'stand' => $myTurn,
                'double' => $myTurn && count($mySeat['cards']) === 2 && CasinoConfigModel::bool('casino_bj_allow_double') && $balance >= $mySeat['bet'],
                'leave' => $myNo > 0,
            ],
            'watchers' => count($state['online']),
            'shoeLeft' => count($state['shoe']),
            'balance' => $balance,
            'minBet' => (int)$table['min_bet'],
            'maxBet' => (int)$table['max_bet'],
            'payout' => CasinoConfigModel::float('casino_bj_payout', 0, 10),
            'hitsSoft17' => CasinoConfigModel::bool('casino_bj_dealer_hits_soft17'),
        ];
    }
}
