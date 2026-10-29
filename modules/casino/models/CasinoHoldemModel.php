<?php

/**
 * No-limit Texas Hold'em for 2 to 9 players who play against each other; the house does not play
 * (an optional rake can be set). Taking a seat moves the buy-in from the balance to the table (the stack);
 * leaving moves the stack back. Big blind = the table's min_bet, small blind = half of it, max_bet = the highest buy-in.
 *
 * Hand: waiting -> preflop -> flop -> turn -> river -> showdown; it ends early when all but one fold.
 * A player who does not act within casino_poker_turn_seconds checks, or folds when a check is not possible;
 * a player who is no longer online does so at once.
 * Raises follow the usual no-limit rules: a raise is at least the size of the last full raise, and an all-in
 * below that does not reopen the betting for players who already acted.
 *
 * State: phase, round (hand number), until, button, sb, bb, turn, board, deck, currentBet, minRaise,
 * raiseNo (counts full raises), settled (the pots of the hand are paid out), pots (showdown), online,
 * seats [no => [uid, name, stack, bet (this street), total (this hand), cards, inHand, folded, allin, needAct,
 *               lastRaiseNo, leaving, last (last action), won, hand (hand name at the showdown), shown]].
 * A card is rank + suit, e.g. "AS", "10H", "QD".
 */
class CasinoHoldemModel
{
    private const SUITS = ['S', 'H', 'D', 'C'];
    private const RANKS = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
    /** Hand categories from low to high; the index is the category number. */
    public const HANDS = ['high_card', 'pair', 'two_pair', 'trips', 'straight', 'flush', 'full_house', 'quads', 'straight_flush', 'royal_flush'];
    private const BETTING = ['preflop', 'flop', 'turn', 'river'];

    public static function normalize(array $state, array $table): array
    {
        $state = array_merge([
            'phase' => 'waiting',
            'round' => 0,
            'until' => 0,
            'button' => 0,
            'sb' => 0,
            'bb' => 0,
            'turn' => 0,
            'board' => [],
            'deck' => [],
            'currentBet' => 0,
            'minRaise' => 0,
            'raiseNo' => 0,
            'settled' => true,
            'pots' => [],
            'seats' => [],
            'online' => [],
        ], $state);
        ksort($state['seats']);
        return $state;
    }

    /** [small blind, big blind] */
    public static function blinds(array $table): array
    {
        $bb = max(2, (int)$table['min_bet']);
        return [max(1, intdiv($bb, 2)), $bb];
    }

    /** [minimum, maximum] buy-in */
    public static function buyinRange(array $table): array
    {
        [, $bb] = self::blinds($table);
        $max = max($bb, (int)$table['max_bet']);
        return [min($max, $bb * CasinoConfigModel::int('casino_poker_min_buyin_bb', 1, 1000)), $max];
    }

    private static function emptySeat(int $uid, string $name, int $stack): array
    {
        return [
            'uid' => $uid, 'name' => $name, 'stack' => $stack, 'bet' => 0, 'total' => 0, 'cards' => [],
            'inHand' => false, 'folded' => false, 'allin' => false, 'needAct' => false, 'lastRaiseNo' => -1,
            'leaving' => false, 'last' => '', 'won' => 0, 'hand' => '', 'shown' => false,
        ];
    }

    private static function canAct(array $seat): bool
    {
        return $seat['inHand'] && !$seat['folded'] && !$seat['allin'];
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

    /** The first seat after $from (clockwise, $from itself last) for which $ok($seat) is true; 0 when none. */
    private static function nextSeat(array $state, int $from, callable $ok): int
    {
        $nos = array_map('intval', array_keys($state['seats']));
        sort($nos);
        $after = array_filter($nos, fn($n) => $n > $from);
        $before = array_filter($nos, fn($n) => $n <= $from);
        foreach (array_merge($after, $before) as $n) {
            if ($ok($state['seats'][$n])) {
                return $n;
            }
        }
        return 0;
    }

    private static function turnSeconds(): int
    {
        return CasinoConfigModel::int('casino_poker_turn_seconds', 5, 300);
    }

    public static function tick(array $table, array &$state): void
    {
        $online = CasinoTableModel::pruneOnline($state);
        $now = time();

        // betting: a player whose time is up, or who is gone, checks or folds
        $guard = 0;
        while (in_array($state['phase'], self::BETTING, true) && $guard++ < 50) {
            $no = (int)$state['turn'];
            $seat = $state['seats'][$no] ?? null;
            if ($seat === null) {
                break;
            }
            $absent = !in_array((int)$seat['uid'], $online, true) || !empty($seat['leaving']);
            if (!$absent && $now < $state['until']) {
                break;
            }
            $action = $seat['bet'] >= $state['currentBet'] ? 'check' : 'fold';
            CasinoLogModel::write((int)$seat['uid'], 'timeout', 0, $action, (int)$table['casino_table_id'], 'holdem', $state['round']);
            self::apply($table, $state, $no, $action, 0);
        }
        if (in_array($state['phase'], self::BETTING, true)) {
            return;
        }

        if ($state['phase'] === 'showdown') {
            if ($now < $state['until']) {
                return;
            }
            self::endHand($state);
        }

        // between hands: who left, went offline or has no chips left stands up (the stack goes back to the balance)
        foreach ($state['seats'] as $no => $seat) {
            if (!empty($seat['leaving'])) {
                self::cashOut($table, $state, (int)$no, 'Van tafel');
            } elseif (!in_array((int)$seat['uid'], $online, true)) {
                self::cashOut($table, $state, (int)$no, 'Geen verbinding meer');
            } elseif ($seat['stack'] <= 0) {
                self::cashOut($table, $state, (int)$no, 'Geen fiches meer');
            }
        }

        if (count($state['seats']) < 2) {
            $state['until'] = 0;
        } elseif ($state['until'] === 0) {
            $state['until'] = $now + 2; // a moment for others to sit down
        } elseif ($now >= $state['until']) {
            self::startHand($table, $state);
        }
    }

    private static function endHand(array &$state): void
    {
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no] = array_merge($seat, [
                'bet' => 0, 'total' => 0, 'cards' => [], 'inHand' => false, 'folded' => false, 'allin' => false,
                'needAct' => false, 'lastRaiseNo' => -1, 'last' => '', 'won' => 0, 'hand' => '', 'shown' => false,
            ]);
        }
        $state['phase'] = 'waiting';
        $state['board'] = [];
        $state['deck'] = [];
        $state['pots'] = [];
        $state['turn'] = 0;
        $state['currentBet'] = 0;
        $state['until'] = 0;
        $state['settled'] = true;
    }

    private static function cashOut(array $table, array &$state, int $no, string $reason): void
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat === null) {
            return;
        }
        unset($state['seats'][$no]);
        $uid = (int)$seat['uid'];
        $tableId = (int)$table['casino_table_id'];
        $amount = (int)$seat['stack'];
        if ($amount > 0) {
            $balance = CasinoUserModel::deposit($uid, $amount);
            CasinoLogModel::write($uid, 'cashout', $amount, $reason . ', stoel ' . $no, $tableId, 'holdem', $state['round'], $balance);
        }
        CasinoLogModel::write($uid, 'leave', 0, $reason . ', stoel ' . $no, $tableId, 'holdem', $state['round']);
    }

    private static function newDeck(): array
    {
        $deck = [];
        foreach (self::SUITS as $suit) {
            foreach (self::RANKS as $rank) {
                $deck[] = $rank . $suit;
            }
        }
        for ($i = count($deck) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$deck[$i], $deck[$j]] = [$deck[$j], $deck[$i]];
        }
        return $deck;
    }

    private static function put(array &$seat, int $amount): void
    {
        $amount = max(0, min($amount, (int)$seat['stack']));
        $seat['stack'] -= $amount;
        $seat['bet'] += $amount;
        $seat['total'] += $amount;
        if ($seat['stack'] === 0) {
            $seat['allin'] = true;
        }
    }

    private static function startHand(array $table, array &$state): void
    {
        [$sbAmount, $bbAmount] = self::blinds($table);
        $tableId = (int)$table['casino_table_id'];
        $state['round']++;
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no] = array_merge($seat, [
                'bet' => 0, 'total' => 0, 'cards' => [], 'inHand' => true, 'folded' => false, 'allin' => false,
                'needAct' => true, 'lastRaiseNo' => -1, 'last' => '', 'won' => 0, 'hand' => '', 'shown' => false,
            ]);
        }

        $any = fn($s) => true;
        $state['button'] = self::nextSeat($state, (int)$state['button'], $any);
        if (count($state['seats']) === 2) {
            // heads-up: the button posts the small blind
            $state['sb'] = $state['button'];
            $state['bb'] = self::nextSeat($state, $state['sb'], $any);
        } else {
            $state['sb'] = self::nextSeat($state, $state['button'], $any);
            $state['bb'] = self::nextSeat($state, $state['sb'], $any);
        }

        $state['deck'] = self::newDeck();
        $state['board'] = [];
        $state['pots'] = [];
        $state['settled'] = false;
        for ($round = 0; $round < 2; $round++) {
            $no = $state['button'];
            for ($i = 0; $i < count($state['seats']); $i++) {
                $no = self::nextSeat($state, $no, $any);
                $state['seats'][$no]['cards'][] = array_pop($state['deck']);
            }
        }

        foreach ([[$state['sb'], $sbAmount, 'sb'], [$state['bb'], $bbAmount, 'bb']] as [$no, $amount, $name]) {
            self::put($state['seats'][$no], $amount);
            $state['seats'][$no]['last'] = $name;
            CasinoLogModel::write((int)$state['seats'][$no]['uid'], 'blind', 0, strtoupper($name) . ' ' . $state['seats'][$no]['bet'], $tableId, 'holdem', $state['round']);
        }
        $state['currentBet'] = $bbAmount;
        $state['minRaise'] = $bbAmount;
        $state['raiseNo'] = 0;
        $state['phase'] = 'preflop';
        $names = array_map(fn($s) => $s['name'] . ' ' . ($s['stack'] + $s['total']), $state['seats']);
        CasinoLogModel::write(0, 'deal', 0, 'Hand #' . $state['round'] . ', spelers: ' . implode(', ', $names), $tableId, 'holdem', $state['round'], 0);

        // the first to act sits after the big blind (heads-up that is the small blind)
        $state['turn'] = $state['bb'];
        self::advance($table, $state);
    }

    /**
     * After an action: ends the hand when one player is left, gives the turn to the next player who has to act,
     * or goes to the next street when the betting round is complete.
     */
    private static function advance(array $table, array &$state): void
    {
        $alive = array_filter($state['seats'], fn($s) => $s['inHand'] && !$s['folded']);
        if (count($alive) <= 1) {
            self::finish($table, $state);
            return;
        }
        $currentBet = $state['currentBet'];
        $next = self::nextSeat($state, (int)$state['turn'], fn($s) => self::canAct($s) && ($s['needAct'] || $s['bet'] < $currentBet));
        if ($next > 0) {
            $state['turn'] = $next;
            $state['until'] = time() + self::turnSeconds();
            return;
        }
        self::nextStreet($table, $state);
    }

    private static function nextStreet(array $table, array &$state): void
    {
        [, $bbAmount] = self::blinds($table);
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no]['bet'] = 0;
            $state['seats'][$no]['needAct'] = self::canAct($seat);
            $state['seats'][$no]['lastRaiseNo'] = -1;
            if (self::canAct($seat)) {
                $state['seats'][$no]['last'] = '';
            }
        }
        $state['currentBet'] = 0;
        $state['minRaise'] = $bbAmount;

        $next = ['preflop' => 'flop', 'flop' => 'turn', 'turn' => 'river', 'river' => 'showdown'][$state['phase']] ?? 'showdown';
        $state['phase'] = $next;
        if ($next === 'showdown') {
            self::finish($table, $state);
            return;
        }
        array_pop($state['deck']); // burn card
        for ($i = 0; $i < ($next === 'flop' ? 3 : 1); $i++) {
            $state['board'][] = array_pop($state['deck']);
        }

        // no betting when fewer than two players can still bet (the others are all-in): deal the rest of the board
        $canAct = array_filter($state['seats'], fn($s) => self::canAct($s));
        if (count($canAct) < 2) {
            self::nextStreet($table, $state);
            return;
        }
        $state['turn'] = $state['button'];
        self::advance($table, $state);
    }

    /** One action of the player in seat $no; throws a CasinoException when it is not allowed. */
    private static function apply(array $table, array &$state, int $no, string $action, int $amount): void
    {
        $s = $state['seats'][$no];
        $toCall = max(0, $state['currentBet'] - $s['bet']);
        $text = $action;
        $detail = '';

        switch ($action) {
            case 'fold':
                $s['folded'] = true;
                break;

            case 'check':
                if ($toCall > 0) {
                    throw new CasinoException(CasinoLang::t('err_pk_cannot_check'));
                }
                break;

            case 'call':
                if ($toCall <= 0) {
                    $text = 'check'; // nothing to call
                    break;
                }
                self::put($s, $toCall);
                $detail = (string)$s['bet'];
                break;

            case 'raise':
            case 'allin':
                $maxTo = $s['bet'] + $s['stack'];
                $to = $action === 'allin' ? $maxTo : min($amount, $maxTo);
                if ($to <= $state['currentBet']) {
                    if ($action !== 'allin') {
                        throw new CasinoException(CasinoLang::t('err_pk_min_raise', ['amount' => min($maxTo, $state['currentBet'] + $state['minRaise'])]));
                    }
                    // all-in for no more than the bet: a call
                    self::put($s, $toCall);
                    $detail = (string)$s['bet'];
                    $text = $s['stack'] === 0 ? 'allin' : 'call';
                    break;
                }
                if ($s['lastRaiseNo'] === $state['raiseNo']) {
                    throw new CasinoException(CasinoLang::t('err_pk_cannot_raise'));
                }
                $minTo = $state['currentBet'] + $state['minRaise'];
                if ($to < $minTo && $to < $maxTo) {
                    throw new CasinoException(CasinoLang::t('err_pk_min_raise', ['amount' => $minTo]));
                }
                $increment = $to - $state['currentBet'];
                if ($increment >= $state['minRaise']) {
                    // a full raise reopens the betting
                    $state['minRaise'] = $increment;
                    $state['raiseNo']++;
                }
                $state['currentBet'] = $to;
                self::put($s, $to - $s['bet']);
                foreach ($state['seats'] as $o => $other) {
                    if ((int)$o !== $no && self::canAct($other)) {
                        $state['seats'][$o]['needAct'] = true;
                    }
                }
                $text = $s['stack'] === 0 ? 'allin' : 'raise';
                $detail = (string)$to;
                break;

            default:
                throw new CasinoException(CasinoLang::t('err_unknown_action'));
        }

        $s['needAct'] = false;
        $s['lastRaiseNo'] = $state['raiseNo'];
        $s['last'] = $text . ($detail !== '' ? ':' . $detail : '');
        $state['seats'][$no] = $s;
        CasinoLogModel::write((int)$s['uid'], $text, 0, trim($detail . ' ' . $state['phase'] . ', stack ' . $s['stack']), (int)$table['casino_table_id'], 'holdem', $state['round']);
        self::advance($table, $state);
    }

    /** Pays out every pot (main pot and side pots) and logs the hand for every player. */
    private static function finish(array $table, array &$state): void
    {
        $tableId = (int)$table['casino_table_id'];
        $alive = array_keys(array_filter($state['seats'], fn($s) => $s['inHand'] && !$s['folded']));
        $showdown = count($alive) > 1;

        // pots: one level per different contribution; only players who did not fold can win a level they reached
        $contrib = [];
        foreach ($state['seats'] as $no => $seat) {
            if ($seat['inHand'] && $seat['total'] > 0) {
                $contrib[(int)$no] = (int)$seat['total'];
            }
        }
        $levels = array_values(array_unique($contrib));
        sort($levels);
        $pots = [];
        $prev = 0;
        foreach ($levels as $level) {
            $amount = 0;
            foreach ($contrib as $c) {
                $amount += min($c, $level) - min($c, $prev);
            }
            $eligible = array_values(array_filter($alive, fn($no) => ($contrib[$no] ?? 0) >= $level));
            $prev = $level;
            if ($amount <= 0) {
                continue;
            }
            $last = count($pots) - 1;
            if ($last >= 0 && $pots[$last]['eligible'] === $eligible) {
                $pots[$last]['amount'] += $amount;
            } else {
                $pots[] = ['amount' => $amount, 'eligible' => $eligible];
            }
        }

        $rake = 0;
        $rakePercent = CasinoConfigModel::int('casino_poker_rake_percent', 0, 10);
        if ($rakePercent > 0 && count($state['board']) >= 3) {
            foreach ($pots as $i => $pot) {
                $r = intdiv($pot['amount'] * $rakePercent, 100);
                $pots[$i]['amount'] -= $r;
                $rake += $r;
            }
        }

        $best = [];
        if ($showdown) {
            foreach ($alive as $no) {
                $best[$no] = self::bestHand(array_merge($state['seats'][$no]['cards'], $state['board']));
                $state['seats'][$no]['shown'] = true;
                $state['seats'][$no]['hand'] = $best[$no]['name'];
            }
        }

        $state['pots'] = [];
        foreach ($pots as $pot) {
            $eligible = $pot['eligible'] ?: $alive;
            if ($showdown) {
                $top = max(array_map(fn($no) => $best[$no]['score'], $eligible));
                $winners = array_values(array_filter($eligible, fn($no) => $best[$no]['score'] === $top));
            } else {
                $winners = $eligible;
            }
            // odd chips go to the first winners after the button
            usort($winners, fn($a, $b) => (($a - $state['button'] + 99) % 100) <=> (($b - $state['button'] + 99) % 100));
            $share = intdiv($pot['amount'], count($winners));
            $rest = $pot['amount'] - $share * count($winners);
            foreach ($winners as $k => $no) {
                $got = $share + ($k < $rest ? 1 : 0);
                $state['seats'][$no]['stack'] += $got;
                $state['seats'][$no]['won'] += $got;
            }
            $state['pots'][] = [
                'amount' => $pot['amount'],
                'winners' => $winners,
                'hand' => $showdown ? $best[$winners[0]]['name'] : '',
            ];
        }

        foreach ($state['seats'] as $no => $seat) {
            if (!$seat['inHand'] || ($seat['total'] === 0 && $seat['won'] === 0)) {
                continue;
            }
            $uid = (int)$seat['uid'];
            $total = (int)$seat['total'];
            $won = (int)$seat['won'];
            $balance = CasinoUserModel::balance($uid);
            CasinoLogModel::write($uid, 'bet', -$total, 'Hand #' . $state['round'] . ', stoel ' . $no, $tableId, 'holdem', $state['round'], $balance);
            $outcome = $won > $total ? 'win' : ($won === $total ? 'push' : 'lose');
            $detail = implode(' ', $seat['cards']) . ' | ' . implode(' ', $state['board']) . ($seat['hand'] !== '' ? ' | ' . $seat['hand'] : '') . ($seat['folded'] ? ' | fold' : '');
            CasinoLogModel::write($uid, $outcome, $won, $detail, $tableId, 'holdem', $state['round'], $balance);
            CasinoUserModel::addStats($uid, $total, max(0, $won - $total));
        }
        if ($rake > 0) {
            CasinoLogModel::write(0, 'rake', $rake, 'Hand #' . $state['round'], $tableId, 'holdem', $state['round'], 0);
        }

        $state['settled'] = true;
        $state['phase'] = 'showdown';
        $state['turn'] = 0;
        $state['until'] = time() + CasinoConfigModel::int('casino_poker_result_seconds', 2, 60);
    }

    /* ---------- hand evaluation ---------- */

    private static function rankValue(string $card): int
    {
        return array_search(substr($card, 0, -1), self::RANKS, true) + 2;
    }

    /** [score, category] of exactly five cards; a higher score is a better hand. */
    public static function score5(array $cards): array
    {
        $ranks = array_map([self::class, 'rankValue'], $cards);
        rsort($ranks);
        $suits = array_map(fn($c) => substr($c, -1), $cards);
        $flush = count(array_unique($suits)) === 1;
        $unique = array_values(array_unique($ranks));
        $straight = 0;
        if (count($unique) === 5) {
            if ($unique[0] - $unique[4] === 4) {
                $straight = $unique[0];
            } elseif ($unique === [14, 5, 4, 3, 2]) {
                $straight = 5; // A-2-3-4-5
            }
        }
        $groups = [];
        foreach (array_count_values($ranks) as $rank => $count) {
            $groups[] = [$count, (int)$rank];
        }
        usort($groups, fn($a, $b) => $b[0] <=> $a[0] ?: $b[1] <=> $a[1]);
        $order = array_column($groups, 1);

        if ($straight && $flush) {
            [$cat, $tie] = [$straight === 14 ? 9 : 8, [$straight]];
        } elseif ($groups[0][0] === 4) {
            [$cat, $tie] = [7, $order];
        } elseif ($groups[0][0] === 3 && $groups[1][0] === 2) {
            [$cat, $tie] = [6, $order];
        } elseif ($flush) {
            [$cat, $tie] = [5, $ranks];
        } elseif ($straight) {
            [$cat, $tie] = [4, [$straight]];
        } elseif ($groups[0][0] === 3) {
            [$cat, $tie] = [3, $order];
        } elseif ($groups[0][0] === 2 && $groups[1][0] === 2) {
            [$cat, $tie] = [2, $order];
        } elseif ($groups[0][0] === 2) {
            [$cat, $tie] = [1, $order];
        } else {
            [$cat, $tie] = [0, $ranks];
        }
        $score = $cat;
        foreach (array_pad($tie, 5, 0) as $v) {
            $score = $score * 15 + $v;
        }
        return [$score, $cat];
    }

    /** The best five of 5 to 7 cards: ['score', 'cat', 'name', 'cards']. */
    public static function bestHand(array $cards): array
    {
        $n = count($cards);
        $best = ['score' => -1, 'cat' => 0, 'name' => self::HANDS[0], 'cards' => []];
        for ($mask = 0; $mask < (1 << $n); $mask++) {
            if (substr_count(decbin($mask), '1') !== 5) {
                continue;
            }
            $five = [];
            for ($i = 0; $i < $n; $i++) {
                if ($mask & (1 << $i)) {
                    $five[] = $cards[$i];
                }
            }
            [$score, $cat] = self::score5($five);
            if ($score > $best['score']) {
                $best = ['score' => $score, 'cat' => $cat, 'name' => self::HANDS[$cat], 'cards' => $five];
            }
        }
        return $best;
    }

    /** What the player holds so far (also with fewer than five cards). */
    private static function currentHand(array $cards): string
    {
        if (count($cards) >= 5) {
            return self::bestHand($cards)['name'];
        }
        $ranks = array_map([self::class, 'rankValue'], $cards);
        return count($ranks) !== count(array_unique($ranks)) ? self::HANDS[1] : self::HANDS[0];
    }

    /* ---------- player actions ---------- */

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
                [$min, $max] = self::buyinRange($table);
                $amount = (int)($post['amount'] ?? 0);
                if ($amount < $min || $amount > $max) {
                    throw new CasinoException(CasinoLang::t('err_pk_buyin', ['min' => $min, 'max' => $max]));
                }
                $seats = max(2, min(CasinoTableModel::MAX_SEATS, (int)$table['max_seats']));
                $wanted = (int)($post['seat'] ?? 0);
                if ($wanted < 1 || $wanted > $seats || isset($state['seats'][$wanted])) {
                    $wanted = 0;
                    for ($i = 1; $i <= $seats; $i++) {
                        if (!isset($state['seats'][$i])) {
                            $wanted = $i;
                            break;
                        }
                    }
                }
                if ($wanted === 0) {
                    throw new CasinoException(CasinoLang::t('err_table_full'));
                }
                $balance = CasinoUserModel::withdraw($uid, $amount);
                $state['seats'][$wanted] = self::emptySeat($uid, CasinoUserModel::publicName($user), $amount);
                ksort($state['seats']);
                CasinoLogModel::write($uid, 'buyin', -$amount, 'Stoel ' . $wanted, $tableId, 'holdem', $state['round'], $balance);
                return;

            case 'leave':
                unset($state['online'][$uid]);
                if ($no === 0) {
                    return;
                }
                $seat = $state['seats'][$no];
                if (in_array($state['phase'], self::BETTING, true) && $seat['inHand']) {
                    // chips of this hand are in the pot: fold now, stand up after the hand
                    $state['seats'][$no]['leaving'] = true;
                    if (!$seat['folded']) {
                        if ((int)$state['turn'] === $no) {
                            self::apply($table, $state, $no, 'fold', 0);
                        } else {
                            $state['seats'][$no]['folded'] = true;
                            $state['seats'][$no]['needAct'] = false;
                            $state['seats'][$no]['last'] = 'fold';
                            CasinoLogModel::write($uid, 'fold', 0, 'Van tafel', $tableId, 'holdem', $state['round']);
                            $alive = array_filter($state['seats'], fn($s) => $s['inHand'] && !$s['folded']);
                            if (count($alive) <= 1) {
                                self::finish($table, $state);
                            }
                        }
                    }
                    return;
                }
                self::cashOut($table, $state, $no, 'Van tafel');
                return;

            case 'fold':
            case 'check':
            case 'call':
            case 'raise':
            case 'allin':
                if (!in_array($state['phase'], self::BETTING, true) || $no === 0 || (int)$state['turn'] !== $no) {
                    throw new CasinoException(CasinoLang::t('err_not_your_turn'));
                }
                self::apply($table, $state, $no, $action, (int)($post['amount'] ?? 0));
                return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    /** Admin reset: every player gets their stack back, plus what they put in a hand that was not paid out yet. */
    public static function refundAll(array $table, array &$state): void
    {
        foreach ($state['seats'] as $no => $seat) {
            $amount = (int)$seat['stack'] + ($state['settled'] ? 0 : (int)$seat['total']);
            if ($amount > 0) {
                $balance = CasinoUserModel::deposit((int)$seat['uid'], $amount);
                CasinoLogModel::write((int)$seat['uid'], 'cashout', $amount, 'Tafel gereset, stoel ' . $no, (int)$table['casino_table_id'], 'holdem', $state['round'], $balance);
            }
        }
        $state['seats'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $myNo = self::seatOf($state, $uid);
        $max = max(2, min(CasinoTableModel::MAX_SEATS, (int)$table['max_seats']));
        $betting = in_array($state['phase'], self::BETTING, true);
        $showdown = $state['phase'] === 'showdown';
        [$sbAmount, $bbAmount] = self::blinds($table);

        $seats = [];
        for ($no = 1; $no <= $max; $no++) {
            $s = $state['seats'][$no] ?? null;
            if ($s === null) {
                $seats[] = ['no' => $no, 'empty' => true];
                continue;
            }
            $mine = $no === $myNo;
            $visible = $mine || !empty($s['shown']);
            $cards = [];
            if ($s['inHand'] && (!$s['folded'] || $mine)) {
                $cards = $visible ? $s['cards'] : array_fill(0, count($s['cards']), '??');
            }
            $seats[] = [
                'no' => $no,
                'empty' => false,
                'name' => $s['name'],
                'me' => $mine,
                'stack' => (int)$s['stack'],
                'bet' => (int)$s['bet'],
                'inHand' => (bool)$s['inHand'],
                'folded' => (bool)$s['folded'],
                'allin' => (bool)$s['allin'],
                'waiting' => !$s['inHand'] && ($betting || $showdown),
                'leaving' => !empty($s['leaving']),
                'cards' => $cards,
                'last' => $s['last'],
                'won' => (int)$s['won'],
                'hand' => $showdown && $visible ? $s['hand'] : '',
                'turn' => $betting && (int)$state['turn'] === $no,
                'dealer' => $no === (int)$state['button'] && ($betting || $showdown),
            ];
        }

        $me = $myNo > 0 ? $state['seats'][$myNo] : null;
        $myTurn = $me !== null && $betting && (int)$state['turn'] === $myNo;
        $toCall = $me ? max(0, $state['currentBet'] - $me['bet']) : 0;
        $maxTo = $me ? $me['bet'] + $me['stack'] : 0;
        $canRaise = $myTurn && $me['lastRaiseNo'] !== $state['raiseNo'] && $maxTo > $state['currentBet'];
        $pot = 0;
        foreach ($state['seats'] as $s) {
            $pot += $s['inHand'] && !$state['settled'] ? (int)$s['total'] : 0;
        }
        $pots = [];
        foreach ($showdown ? $state['pots'] : [] as $p) {
            $pots[] = [
                'amount' => $p['amount'],
                'winners' => array_map(fn($no) => $state['seats'][$no]['name'] ?? '?', $p['winners']),
                'mine' => in_array($myNo, $p['winners'], true),
                'hand' => $p['hand'],
            ];
        }
        [$minBuy, $maxBuy] = self::buyinRange($table);

        return [
            'game' => 'holdem',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => CasinoTableModel::remaining($state),
            'turnSeconds' => self::turnSeconds(),
            'seats' => $seats,
            'board' => $state['board'],
            'pot' => $showdown ? array_sum(array_column($pots, 'amount')) : $pot,
            'pots' => $pots,
            'currentBet' => (int)$state['currentBet'],
            'mySeat' => $myNo,
            'myHand' => $me && $me['inHand'] && !$me['folded'] && $me['cards'] ? self::currentHand(array_merge($me['cards'], $state['board'])) : '',
            'toCall' => $me ? min($toCall, (int)$me['stack']) : 0,
            'minRaiseTo' => $me ? min($maxTo, $state['currentBet'] + $state['minRaise']) : 0,
            'maxRaiseTo' => $maxTo,
            'can' => [
                'sit' => $myNo === 0 && count($state['seats']) < $max,
                'fold' => $myTurn,
                'check' => $myTurn && $toCall === 0,
                'call' => $myTurn && $toCall > 0,
                'raise' => $canRaise,
                'allin' => $myTurn && $me['stack'] > 0 && ($canRaise || $me['stack'] <= $toCall),
                'leave' => $myNo > 0,
            ],
            'blinds' => [$sbAmount, $bbAmount],
            'buyin' => [$minBuy, $maxBuy],
            'watchers' => count($state['online']),
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => $bbAmount,
            'maxBet' => $maxBuy,
        ];
    }
}
