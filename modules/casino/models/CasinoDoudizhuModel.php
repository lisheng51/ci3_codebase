<?php

/**
 * Dou Dizhu (斗地主, "fight the landlord") for three players: one landlord against two farmers. The house does not
 * play. Taking a seat moves the buy-in from the balance to the table (the stack); leaving moves the stack back,
 * as at the poker table. The table's min_bet is the base stake, max_bet the highest buy-in.
 *
 * Hand: waiting -> bidding -> playing -> result.
 * - Bidding: from a random player, everyone may once bid 1, 2 or 3 points (higher than the bid so far) or pass;
 *   3 ends the bidding. The highest bidder becomes landlord and takes the 3 bottom cards (shown to all).
 *   Nobody bids: the cards are dealt again.
 * - Playing: the landlord leads; the next player plays a higher play of the same kind (or a bomb / the rocket)
 *   or passes. After two passes the last player to play leads again. The first to play all cards wins for their side.
 * - Score: base stake x bid x casino_ddz_bomb_mult per bomb x casino_ddz_rocket_mult for the rocket (王炸)
 *   x 2 for a spring (the other side never played a card, or the landlord only played the first lead),
 *   up to casino_ddz_max_mult. Each farmer pays the landlord or is
 *   paid by the landlord this amount; nobody pays more than their stack.
 *
 * Shared table: a player who does not act within casino_ddz_turn_seconds, or who went away, is played by the
 * computer (托管) until they act again. Solo table: the player plays against two computer players, whose chips are
 * the house's (they never run out); the player starts each hand.
 *
 * State: phase, round, until, first (first bidder), turn, bid, bidder, asked (players asked to bid), deals
 * (deals without a bid), landlord, bottom, last (the play to beat: [seat, type, rank, n, cards]), passes, bombs,
 * rockets, boost (what the bombs and the rocket multiply the score by), plays ([seat => plays]), winner, mult, online,
 * seats [no => [uid, name, stack, bot, cards, auto, leaving, move ('' / 'pass' / 'bid0' .. 'bid3' / 'play'),
 *               played (cards of the last play), result (chips won or lost in the last hand)]].
 */
class CasinoDoudizhuModel
{
    public const SEATS = 3;
    private const ACTIVE = ['bidding', 'playing'];
    /** Seconds a computer player waits before it plays, so the moves can be followed. */
    private const BOT_DELAY = 1;

    public static function normalize(array $state, array $table): array
    {
        $state = array_merge([
            'phase' => 'waiting',
            'round' => 0,
            'until' => 0,
            'first' => 0,
            'turn' => 0,
            'bid' => 0,
            'bidder' => 0,
            'asked' => 0,
            'deals' => 0,
            'landlord' => 0,
            'bottom' => [],
            'last' => null,
            'passes' => 0,
            'bombs' => 0,
            'rockets' => 0,
            'boost' => 1,
            'plays' => [],
            'winner' => 0,
            'mult' => 0,
            'seats' => [],
            'online' => [],
        ], $state);
        ksort($state['seats']);
        return $state;
    }

    public static function base(array $table): int
    {
        return max(1, (int)$table['min_bet']);
    }

    /** [minimum, maximum] buy-in */
    public static function buyinRange(array $table): array
    {
        $base = self::base($table);
        $max = max($base, (int)$table['max_bet']);
        return [min($max, $base * CasinoConfigModel::int('casino_ddz_min_buyin', 1, 10000)), $max];
    }

    private static function maxMult(): int
    {
        return CasinoConfigModel::int('casino_ddz_max_mult', 1, 100000);
    }

    /** What a bomb or the rocket multiplies the score by. */
    private static function boostOf(string $type): int
    {
        return $type === 'rocket'
            ? CasinoConfigModel::int('casino_ddz_rocket_mult', 1, 100)
            : CasinoConfigModel::int('casino_ddz_bomb_mult', 1, 100);
    }

    private static function turnSeconds(): int
    {
        return CasinoConfigModel::int('casino_ddz_turn_seconds', 5, 300);
    }

    private static function newSeat(int $uid, string $name, int $stack, bool $bot = false): array
    {
        return ['uid' => $uid, 'name' => $name, 'stack' => $stack, 'bot' => $bot, 'cards' => [], 'auto' => false,
            'leaving' => false, 'move' => '', 'played' => [], 'result' => 0];
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

    private static function next(int $no): int
    {
        return $no % self::SEATS + 1;
    }

    private static function log(array $table, array $state, int $no, string $action, int $amount, string $detail, int $balance = -1): void
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat !== null && empty($seat['bot'])) {
            CasinoLogModel::write((int)$seat['uid'], $action, $amount, $detail, (int)$table['casino_table_id'], 'doudizhu', (int)$state['round'], $balance);
        }
    }

    /* ---------- the course of a hand ---------- */

    public static function tick(array $table, array &$state): void
    {
        $online = CasinoTableModel::pruneOnline($state);
        $now = time();

        // a player whose time is up, who went away or who is played by the computer: the computer moves
        $guard = 0;
        while (in_array($state['phase'], self::ACTIVE, true) && $guard++ < 200) {
            $no = (int)$state['turn'];
            $seat = $state['seats'][$no] ?? null;
            if ($seat === null) {
                break;
            }
            $absent = !in_array((int)$seat['uid'], $online, true) || !empty($seat['leaving']);
            if (!$absent && $now < $state['until']) {
                break;
            }
            if (!$absent && empty($seat['auto'])) {
                $state['seats'][$no]['auto'] = true; // time is up: the computer plays from now on
                self::log($table, $state, $no, 'timeout', 0, 'Automatisch spelen');
            }
            self::aiAct($table, $state, $no);
        }
        if (in_array($state['phase'], self::ACTIVE, true)) {
            return;
        }

        if ($state['phase'] === 'result') {
            if ($now < $state['until']) {
                return;
            }
            self::endHand($state);
        }

        // between hands: who left, went offline or cannot pay the base stake stands up
        foreach ($state['seats'] as $no => $seat) {
            if (!empty($seat['leaving'])) {
                self::cashOut($table, $state, (int)$no, 'Van tafel');
            } elseif (!in_array((int)$seat['uid'], $online, true)) {
                self::cashOut($table, $state, (int)$no, 'Geen verbinding meer');
            } elseif ($seat['stack'] < self::base($table)) {
                self::cashOut($table, $state, (int)$no, 'Geen fiches meer');
            }
        }

        if (count($state['seats']) < self::SEATS) {
            $state['until'] = 0;
        } elseif ($state['until'] === 0) {
            $state['until'] = $now + 2;
        } elseif ($now >= $state['until']) {
            self::startHand($table, $state);
        }
    }

    private static function endHand(array &$state): void
    {
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no] = array_merge($seat, ['cards' => [], 'move' => '', 'played' => []]);
        }
        $state = array_merge($state, [
            'phase' => 'waiting', 'until' => 0, 'turn' => 0, 'bid' => 0, 'bidder' => 0, 'asked' => 0, 'landlord' => 0,
            'bottom' => [], 'last' => null, 'passes' => 0, 'bombs' => 0, 'rockets' => 0, 'boost' => 1, 'plays' => [], 'winner' => 0, 'mult' => 0,
        ]);
    }

    private static function cashOut(array $table, array &$state, int $no, string $reason): void
    {
        $seat = $state['seats'][$no] ?? null;
        if ($seat === null) {
            return;
        }
        unset($state['seats'][$no]);
        if (!empty($seat['bot'])) {
            return;
        }
        $uid = (int)$seat['uid'];
        $tableId = (int)$table['casino_table_id'];
        $amount = (int)$seat['stack'];
        if ($amount > 0) {
            $balance = CasinoUserModel::deposit($uid, $amount);
            CasinoLogModel::write($uid, 'cashout', $amount, $reason . ', stoel ' . $no, $tableId, 'doudizhu', $state['round'], $balance);
        }
        CasinoLogModel::write($uid, 'leave', 0, $reason . ', stoel ' . $no, $tableId, 'doudizhu', $state['round']);
    }

    /** Deals 17 cards each and 3 bottom cards, and starts the bidding at a random player. */
    private static function startHand(array $table, array &$state): void
    {
        $state['round']++;
        $state['deals'] = 0;
        self::deal($table, $state);
    }

    private static function deal(array $table, array &$state): void
    {
        $deck = CasinoDdzCards::deck();
        foreach ($state['seats'] as $no => $seat) {
            $state['seats'][$no] = array_merge($seat, [
                'cards' => CasinoDdzCards::sort(array_splice($deck, 0, 17)), 'move' => '', 'played' => [], 'result' => 0,
            ]);
        }
        $state['bottom'] = $deck;
        $state = array_merge($state, ['phase' => 'bidding', 'bid' => 0, 'bidder' => 0, 'asked' => 0, 'landlord' => 0,
            'last' => null, 'passes' => 0, 'bombs' => 0, 'rockets' => 0, 'boost' => 1, 'plays' => [1 => 0, 2 => 0, 3 => 0], 'winner' => 0, 'mult' => 0]);
        $state['first'] = random_int(1, self::SEATS);
        $names = implode(', ', array_map(fn($s) => $s['name'], $state['seats']));
        CasinoLogModel::write(0, 'deal', 0, 'Hand #' . $state['round'] . ', spelers: ' . $names, (int)$table['casino_table_id'], 'doudizhu', $state['round'], 0);
        self::giveTurn($state, (int)$state['first']);
    }

    private static function giveTurn(array &$state, int $no): void
    {
        $state['turn'] = $no;
        $seat = $state['seats'][$no];
        if ($state['phase'] === 'playing') {
            // what this player did the last time round goes off the table now it is their turn again
            $state['seats'][$no]['move'] = '';
            $state['seats'][$no]['played'] = [];
        }
        // the computer plays after a moment; a player in solo has all the time they want
        $state['until'] = !empty($seat['bot']) || !empty($seat['auto']) ? time() + self::BOT_DELAY
            : (!empty($state['solo']) ? PHP_INT_MAX : time() + self::turnSeconds());
    }

    private static function bid(array $table, array &$state, int $no, int $points): void
    {
        if ($points !== 0 && ($points <= $state['bid'] || $points > 3)) {
            throw new CasinoException(CasinoLang::t('err_ddz_bid'));
        }
        $state['asked']++;
        $state['seats'][$no]['move'] = 'bid' . $points;
        self::log($table, $state, $no, 'bid', 0, $points ? $points . ' punt' : 'Pas');
        if ($points > 0) {
            $state['bid'] = $points;
            $state['bidder'] = $no;
        }
        if ($state['bid'] === 3 || $state['asked'] >= self::SEATS) {
            if ($state['bid'] === 0) {
                // nobody wants to be landlord: deal again (after a few tries the first player has to)
                if (++$state['deals'] < 3) {
                    self::deal($table, $state);
                    return;
                }
                $state['bid'] = 1;
                $state['bidder'] = (int)$state['first'];
            }
            self::startPlay($table, $state);
            return;
        }
        self::giveTurn($state, self::next($no));
    }

    private static function startPlay(array $table, array &$state): void
    {
        $no = (int)$state['bidder'];
        $state['landlord'] = $no;
        $state['seats'][$no]['cards'] = CasinoDdzCards::sort(array_merge($state['seats'][$no]['cards'], $state['bottom']));
        foreach ($state['seats'] as $n => $seat) {
            $state['seats'][$n]['move'] = '';
        }
        $state['phase'] = 'playing';
        $state['last'] = null;
        $state['passes'] = 0;
        self::log($table, $state, $no, 'landlord', 0, 'Landheer, bod ' . $state['bid'] . ', onderkaarten ' . implode(' ', $state['bottom']));
        self::giveTurn($state, $no);
    }

    /** Plays $cards (or passes with []) for seat $no. */
    private static function playCards(array $table, array &$state, int $no, array $cards): void
    {
        $seat = $state['seats'][$no];
        $last = $state['last'];
        $leading = $last === null;
        if (!$cards) {
            if ($leading) {
                throw new CasinoException(CasinoLang::t('err_ddz_must_lead'));
            }
            $state['seats'][$no]['move'] = 'pass';
            $state['seats'][$no]['played'] = [];
            self::log($table, $state, $no, 'pass', 0, 'Pas');
            if (++$state['passes'] >= 2) {
                // two passes: the last player to play leads again, with a clean table
                $lead = (int)$last['seat'];
                $state['last'] = null;
                $state['passes'] = 0;
                foreach ($state['seats'] as $n => $s) {
                    $state['seats'][$n]['move'] = '';
                    $state['seats'][$n]['played'] = [];
                }
                self::giveTurn($state, $lead);
                return;
            }
            self::giveTurn($state, self::next($no));
            return;
        }

        if (array_diff($cards, $seat['cards']) || count(array_unique($cards)) !== count($cards)) {
            throw new CasinoException(CasinoLang::t('err_ddz_cards'));
        }
        $play = CasinoDdzCards::read($cards, $leading ? null : $last);
        if ($play === null) {
            throw new CasinoException(CasinoLang::t($leading || !CasinoDdzCards::analyze($cards) ? 'err_ddz_invalid' : 'err_ddz_lower'));
        }
        $state['seats'][$no]['cards'] = array_values(array_diff($seat['cards'], $cards));
        $state['seats'][$no]['move'] = 'play';
        $state['seats'][$no]['played'] = $play['cards'];
        $state['last'] = ['seat' => $no, 'type' => $play['type'], 'rank' => $play['rank'], 'n' => $play['n'], 'cards' => $play['cards']];
        $state['passes'] = 0;
        $state['plays'][$no] = ($state['plays'][$no] ?? 0) + 1;
        // bombs and the rocket multiply the score (the rocket counts extra)
        if ($play['type'] === 'bomb' || $play['type'] === 'rocket') {
            $state[$play['type'] === 'bomb' ? 'bombs' : 'rockets']++;
            $state['boost'] = min(self::maxMult(), (int)$state['boost'] * self::boostOf($play['type']));
        }
        self::log($table, $state, $no, 'play', 0, $play['type'] . ' ' . implode(' ', $play['cards']));

        if (!$state['seats'][$no]['cards']) {
            self::finish($table, $state, $no);
            return;
        }
        self::giveTurn($state, self::next($no));
    }

    /** The computer moves for seat $no (a computer player, or a player who is away or ran out of time). */
    private static function aiAct(array $table, array &$state, int $no): void
    {
        $seat = $state['seats'][$no];
        if ($state['phase'] === 'bidding') {
            self::bid($table, $state, $no, CasinoDdzCards::aiBid($seat['cards'], (int)$state['bid']));
            return;
        }
        $landlord = (int)$state['landlord'];
        $last = $state['last'];
        $partner = $last !== null && $no !== $landlord && (int)$last['seat'] !== $landlord;
        $opponents = $no === $landlord ? array_diff(range(1, self::SEATS), [$no]) : [$landlord];
        $opponentMin = min(array_map(fn($n) => count($state['seats'][$n]['cards']), $opponents));
        $nextNo = self::next($no);
        $move = CasinoDdzCards::aiMove($seat['cards'], $last, $partner, $opponentMin, in_array($nextNo, $opponents, true), count($state['seats'][$nextNo]['cards']));
        self::playCards($table, $state, $no, $move);
    }

    /** Somebody played their last card: the score is worked out and paid between the stacks. */
    private static function finish(array $table, array &$state, int $winner): void
    {
        $landlord = (int)$state['landlord'];
        $landlordWins = $winner === $landlord;
        $farmers = [self::next($landlord), self::next(self::next($landlord))];
        $spring = $landlordWins
            ? array_sum(array_map(fn($n) => (int)($state['plays'][$n] ?? 0), $farmers)) === 0
            : (int)($state['plays'][$landlord] ?? 0) <= 1;
        $mult = (int)$state['bid'] * (int)$state['boost'] * ($spring ? 2 : 1);
        $mult = min($mult, self::maxMult());
        $unit = self::base($table) * $mult;
        // a computer player never runs out of chips
        $can = fn(int $n) => !empty($state['seats'][$n]['bot']) ? PHP_INT_MAX : (int)$state['seats'][$n]['stack'];

        $paid = [];  // seat => chips paid (negative) or received
        $stake = []; // seat => what the seat had at stake
        foreach ($farmers as $f) {
            $stake[$f] = min($unit, $can($f));
        }
        $stake[$landlord] = min(2 * $unit, $can($landlord));
        if ($landlordWins) {
            $total = 0;
            foreach ($farmers as $f) {
                $paid[$f] = -$stake[$f];
                $total += $stake[$f];
            }
            $paid[$landlord] = $total;
        } else {
            $total = $stake[$landlord];
            $paid[$landlord] = -$total;
            $half = intdiv($total, 2);
            $paid[$farmers[0]] = $total - $half; // an odd chip goes to the farmer after the landlord
            $paid[$farmers[1]] = $half;
        }

        $tableId = (int)$table['casino_table_id'];
        foreach ($paid as $no => $amount) {
            $seat = $state['seats'][$no];
            $state['seats'][$no]['result'] = $amount;
            if (!empty($seat['bot'])) {
                continue;
            }
            $state['seats'][$no]['stack'] = (int)$seat['stack'] + $amount;
            $uid = (int)$seat['uid'];
            $role = $no === $landlord ? 'landlord' : 'farmer';
            $won = $amount > 0;
            $detail = $role . ' x' . $mult . ($spring ? ' spring' : '') . ' | bod ' . $state['bid'] . ', bommen ' . $state['bombs'] . ', raket ' . $state['rockets']
                . ' | stack ' . $state['seats'][$no]['stack'];
            // records: the stake as the bet, what came back as the result
            CasinoLogModel::write($uid, 'bet', -$stake[$no], 'Hand #' . $state['round'] . ', stoel ' . $no, $tableId, 'doudizhu', $state['round']);
            CasinoLogModel::write($uid, $won ? 'win' : 'lose', $stake[$no] + $amount, $detail, $tableId, 'doudizhu', $state['round']);
            CasinoUserModel::addStats($uid, $stake[$no], max(0, $amount));
        }

        $state['winner'] = $winner;
        $state['mult'] = $mult;
        $state['spring'] = $spring;
        $state['phase'] = 'result';
        $state['turn'] = 0;
        $state['until'] = time() + CasinoConfigModel::int('casino_ddz_result_seconds', 2, 60);
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
                $amount = (int)($post['amount'] ?? 0);
                [$min, $max] = self::buyinRange($table);
                if ($amount < $min || $amount > $max) {
                    throw new CasinoException(CasinoLang::t('err_pk_buyin', ['min' => $min, 'max' => $max]));
                }
                $wanted = (int)($post['seat'] ?? 0);
                if ($wanted < 1 || $wanted > self::SEATS || isset($state['seats'][$wanted])) {
                    $free = array_diff(range(1, self::SEATS), array_keys($state['seats']));
                    $wanted = (int)(reset($free) ?: 0);
                }
                if ($wanted === 0) {
                    throw new CasinoException(CasinoLang::t('err_table_full'));
                }
                $balance = CasinoUserModel::withdraw($uid, $amount);
                $state['seats'][$wanted] = self::newSeat($uid, CasinoUserModel::publicName($user), $amount);
                ksort($state['seats']);
                CasinoLogModel::write($uid, 'buyin', -$amount, 'Stoel ' . $wanted, $tableId, 'doudizhu', $state['round'], $balance);
                return;

            case 'leave':
                unset($state['online'][$uid]);
                if ($no === 0) {
                    return;
                }
                if (in_array($state['phase'], self::ACTIVE, true)) {
                    // the hand goes on, played by the computer; the player stands up after it
                    $state['seats'][$no]['leaving'] = true;
                    $state['seats'][$no]['auto'] = true;
                    if ((int)$state['turn'] === $no) {
                        $state['until'] = 0;
                    }
                    return;
                }
                self::cashOut($table, $state, $no, 'Van tafel');
                return;

            case 'auto':
                // 托管 on or off
                if ($no > 0) {
                    $on = !empty($post['on']);
                    $state['seats'][$no]['auto'] = $on;
                    if ((int)$state['turn'] === $no && in_array($state['phase'], self::ACTIVE, true)) {
                        self::giveTurn($state, $no);
                    }
                }
                return;

            case 'bid':
            case 'play':
            case 'pass':
                $phase = $action === 'bid' ? 'bidding' : 'playing';
                if ($state['phase'] !== $phase || $no === 0 || (int)$state['turn'] !== $no) {
                    throw new CasinoException(CasinoLang::t('err_not_your_turn'));
                }
                $state['seats'][$no]['auto'] = false; // the player is back
                if ($action === 'bid') {
                    self::bid($table, $state, $no, max(0, (int)($post['points'] ?? 0)));
                } else {
                    $cards = $action === 'pass' ? [] : array_values(array_filter(explode(',', (string)($post['cards'] ?? ''))));
                    if ($action === 'play' && !$cards) {
                        throw new CasinoException(CasinoLang::t('err_ddz_pick'));
                    }
                    self::playCards($table, $state, $no, $cards);
                }
                return;
        }

        throw new CasinoException(CasinoLang::t('err_unknown_action'));
    }

    /* ---------- solo: the player against two computer players ---------- */

    /** Before every poll and action of a solo player: the computer players move when it is their turn. */
    public static function soloTick(array $table, array &$state, array $user): void
    {
        $guard = 0;
        while (in_array($state['phase'], self::ACTIVE, true) && $guard++ < 10) {
            $no = (int)$state['turn'];
            $seat = $state['seats'][$no] ?? null;
            if ($seat === null || (empty($seat['bot']) && empty($seat['auto'])) || time() < $state['until']) {
                break;
            }
            self::aiAct($table, $state, $no);
        }
    }

    public static function soloAct(array $table, array &$state, array $user, string $action, array $post): void
    {
        $uid = (int)$user['casino_user_id'];
        $state['solo'] = true;
        $no = self::seatOf($state, $uid);
        switch ($action) {
            case 'sit':
                if ($no > 0) {
                    return;
                }
                $state['seats'] = [];
                self::act($table, $state, $user, 'sit', ['amount' => $post['amount'] ?? 0, 'seat' => 1]);
                foreach ([2, 3] as $i => $n) {
                    $state['seats'][$n] = self::newSeat(0, CasinoLang::t('ddz_bot_' . ($i + 1)), 0, true);
                }
                ksort($state['seats']);
                return;

            case 'go':
                // the next hand, when the player can pay the base stake
                if ($no === 0 || in_array($state['phase'], self::ACTIVE, true)) {
                    return;
                }
                if ($state['seats'][$no]['stack'] < self::base($table)) {
                    throw new CasinoException(CasinoLang::t('err_ddz_stack'));
                }
                self::endHand($state);
                self::startHand($table, $state);
                return;

            case 'leave':
                if ($no === 0) {
                    return;
                }
                // a hand in play is finished by the computer first, so leaving does not dodge a loss
                $guard = 0;
                $state['seats'][$no]['auto'] = true;
                while (in_array($state['phase'], self::ACTIVE, true) && $guard++ < 300) {
                    self::aiAct($table, $state, (int)$state['turn']);
                }
                self::cashOut($table, $state, $no, 'Van tafel');
                $state['seats'] = [];
                self::endHand($state);
                return;
        }
        self::act($table, $state, $user, $action, $post);
    }

    /** Admin reset: every player gets their stack back (chips only move between stacks at the end of a hand). */
    public static function refundAll(array $table, array &$state): void
    {
        foreach ($state['seats'] as $no => $seat) {
            if (empty($seat['bot']) && (int)$seat['stack'] > 0) {
                $balance = CasinoUserModel::deposit((int)$seat['uid'], (int)$seat['stack']);
                CasinoLogModel::write((int)$seat['uid'], 'cashout', (int)$seat['stack'], 'Tafel gereset, stoel ' . $no, (int)$table['casino_table_id'], 'doudizhu', $state['round'], $balance);
            }
        }
        $state['seats'] = [];
    }

    public static function view(array $table, array $state, array $user): array
    {
        $uid = (int)$user['casino_user_id'];
        $myNo = self::seatOf($state, $uid);
        $active = in_array($state['phase'], self::ACTIVE, true);
        $result = $state['phase'] === 'result';
        $landlord = (int)$state['landlord'];

        $seats = [];
        for ($no = 1; $no <= self::SEATS; $no++) {
            $s = $state['seats'][$no] ?? null;
            if ($s === null) {
                $seats[] = ['no' => $no, 'empty' => true];
                continue;
            }
            $mine = $no === $myNo;
            $seats[] = [
                'no' => $no,
                'empty' => false,
                'name' => $s['name'],
                'me' => $mine,
                'bot' => !empty($s['bot']),
                'stack' => empty($s['bot']) ? (int)$s['stack'] : null,
                'count' => count($s['cards']),
                // the cards of the others are shown when the hand is over
                'cards' => $mine || $result ? $s['cards'] : [],
                'landlord' => $landlord === $no,
                'turn' => $active && (int)$state['turn'] === $no,
                'auto' => !empty($s['auto']),
                'leaving' => !empty($s['leaving']),
                'move' => $s['move'],
                'played' => $s['played'],
                'result' => $result ? (int)$s['result'] : 0,
            ];
        }

        $me = $myNo > 0 ? $state['seats'][$myNo] : null;
        $myTurn = $me !== null && $active && (int)$state['turn'] === $myNo;
        $leading = $state['phase'] === 'playing' && $state['last'] === null;
        // the hint: what the computer would play; empty when nothing beats the last play
        $hint = $myTurn && $state['phase'] === 'playing' ? CasinoDdzCards::aiMove($me['cards'], $state['last'], false, 20, true, 20) : [];
        $bid = (int)$state['bid'];
        $mult = $result ? (int)$state['mult'] : max(1, $bid) * (int)$state['boost'];
        [$minBuy, $maxBuy] = self::buyinRange($table);
        $solo = !empty($state['solo']);

        return [
            'game' => 'doudizhu',
            'phase' => $state['phase'],
            'round' => $state['round'],
            'remaining' => $solo ? null : CasinoTableModel::remaining($state),
            'turnSeconds' => self::turnSeconds(),
            'seats' => $seats,
            'mySeat' => $myNo,
            'landlord' => $landlord,
            'bottom' => $landlord > 0 || $result ? $state['bottom'] : ($state['phase'] === 'bidding' ? ['??', '??', '??'] : []),
            'bid' => $bid,
            'bombs' => (int)$state['bombs'],
            'rockets' => (int)$state['rockets'],
            'bombMult' => self::boostOf('bomb'),
            'rocketMult' => self::boostOf('rocket'),
            'mult' => min($mult, self::maxMult()),
            'spring' => !empty($state['spring']) && $result,
            'winner' => (int)$state['winner'],
            'last' => $state['last'],
            'hint' => $hint,
            'can' => [
                'sit' => $myNo === 0 && count($state['seats']) < self::SEATS,
                'bid' => $myTurn && $state['phase'] === 'bidding' ? array_values(array_filter([1, 2, 3], fn($p) => $p > $bid)) : [],
                'play' => $myTurn && $state['phase'] === 'playing',
                'pass' => $myTurn && $state['phase'] === 'playing' && !$leading,
                'go' => $solo && $myNo > 0 && !$active,
                'auto' => $myNo > 0 && $active,
                'leave' => $myNo > 0,
            ],
            'base' => self::base($table),
            'maxMult' => self::maxMult(),
            'buyin' => [$minBuy, $maxBuy],
            'watchers' => count($state['online']),
            'balance' => (int)($user['balance'] ?? 0),
            'minBet' => self::base($table),
            'maxBet' => $maxBuy,
        ];
    }
}
