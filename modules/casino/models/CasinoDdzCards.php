<?php

/**
 * Dou Dizhu (斗地主) cards: what a set of cards is as a play, which play beats which, and a computer player
 * (used for the computer opponents in solo and for players who ran out of time or went away).
 *
 * 54 cards: 3 to 2 in four suits plus two jokers. A card is rank + suit ("3S", "10H", "2D"),
 * "LJ" = little joker, "BJ" = big joker. Order: 3 < 4 < ... < K < A < 2 < little joker < big joker.
 *
 * A play is ['type', 'rank' (the value that counts when comparing), 'n' (number of cards), 'cards'].
 * Types: single, pair, triple, triple1 (3 + 1), triple2 (3 + pair), straight (5+ singles in a row, 3 to A),
 * pairs (3+ pairs in a row), plane (2+ triples in a row), plane1 (plane + one card per triple),
 * plane2 (plane + one pair per triple), four2 (4 + 2 cards), four2pairs (4 + 2 pairs), bomb (4 of a kind),
 * rocket (both jokers).
 */
class CasinoDdzCards
{
    public const TYPES = ['single', 'pair', 'triple', 'triple1', 'triple2', 'straight', 'pairs', 'plane', 'plane1', 'plane2', 'four2', 'four2pairs', 'bomb', 'rocket'];
    private const RANKS = ['3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A', '2'];
    private const SUITS = ['S', 'H', 'C', 'D'];
    /** The highest value in a row (straight, pairs, plane): A. */
    private const TOP_IN_ROW = 14;

    public static function deck(): array
    {
        $deck = ['LJ', 'BJ'];
        foreach (self::RANKS as $rank) {
            foreach (self::SUITS as $suit) {
                $deck[] = $rank . $suit;
            }
        }
        for ($i = count($deck) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$deck[$i], $deck[$j]] = [$deck[$j], $deck[$i]];
        }
        return $deck;
    }

    /** 3..14 for 3..A, 15 for 2, 16 little joker, 17 big joker. */
    public static function value(string $card): int
    {
        if ($card === 'LJ') {
            return 16;
        }
        if ($card === 'BJ') {
            return 17;
        }
        $rank = substr($card, 0, -1);
        return ['J' => 11, 'Q' => 12, 'K' => 13, 'A' => 14, '2' => 15][$rank] ?? (int)$rank;
    }

    /** High to low, as a hand is held. */
    public static function sort(array $cards): array
    {
        usort($cards, fn($a, $b) => [self::value($b), $b] <=> [self::value($a), $a]);
        return $cards;
    }

    /** value => number of cards */
    private static function counts(array $cards): array
    {
        $counts = [];
        foreach ($cards as $c) {
            $v = self::value($c);
            $counts[$v] = ($counts[$v] ?? 0) + 1;
        }
        ksort($counts);
        return $counts;
    }

    /** True when the values follow each other without a gap and do not go past A. */
    private static function inRow(array $values): bool
    {
        sort($values);
        foreach ($values as $i => $v) {
            if ($v > self::TOP_IN_ROW || ($i > 0 && $v !== $values[$i - 1] + 1)) {
                return false;
            }
        }
        return true;
    }

    private static function play(string $type, int $rank, array $cards): array
    {
        return ['type' => $type, 'rank' => $rank, 'n' => count($cards), 'cards' => self::sort($cards)];
    }

    /**
     * Every way these cards can be read as a play, the most natural first; [] when they are not a play.
     * (333444555666 is a plane of four, but also a plane of three with 666 as wings.)
     */
    public static function analyze(array $cards): array
    {
        $n = count($cards);
        if ($n === 0 || count(array_unique($cards)) !== $n) {
            return [];
        }
        $counts = self::counts($cards);
        $values = array_keys($counts);
        $by = []; // number of cards => values with that many
        foreach ($counts as $v => $k) {
            $by[$k][] = $v;
        }
        $out = [];
        $add = function (string $type, int $rank) use (&$out, $cards) {
            $out[] = self::play($type, $rank, $cards);
        };

        if ($n === 2 && isset($counts[16], $counts[17])) {
            $add('rocket', 17);
            return $out;
        }
        if ($n === 4 && count($counts) === 1) {
            $add('bomb', $values[0]);
            return $out;
        }
        if (count($counts) === 1 && $n <= 3) {
            $add(['', 'single', 'pair', 'triple'][$n], $values[0]);
            return $out;
        }
        if ($n === 4 && isset($by[3])) {
            $add('triple1', $by[3][0]);
        }
        if ($n === 5 && isset($by[3], $by[2])) {
            $add('triple2', $by[3][0]);
        }
        if ($n >= 5 && count($counts) === $n && self::inRow($values)) {
            $add('straight', max($values));
        }
        if ($n >= 6 && $n % 2 === 0 && count($by[2] ?? []) === $n / 2 && self::inRow($values)) {
            $add('pairs', max($values));
        }
        if ($n >= 6 && $n % 3 === 0 && count($by[3] ?? []) === $n / 3 && self::inRow($values)) {
            $add('plane', max($values));
        }
        // planes with wings: k triples in a row, plus k single cards or k pairs that are not part of the plane
        foreach ([[4, 'plane1'], [5, 'plane2']] as [$size, $type]) {
            if ($n < 2 * $size || $n % $size !== 0) {
                continue;
            }
            $k = intdiv($n, $size);
            $triples = array_keys(array_filter($counts, fn($c) => $c === 3));
            for ($i = 0; $i + $k <= count($triples); $i++) {
                $chain = array_slice($triples, $i, $k);
                if (!self::inRow($chain)) {
                    continue;
                }
                $rest = array_diff_key($counts, array_flip($chain));
                if ($type === 'plane1' && isset($rest[16], $rest[17])) {
                    continue; // both jokers are a rocket, not two wings
                }
                if ($type === 'plane2' && (isset($rest[16]) || isset($rest[17]) || array_filter($rest, fn($c) => $c % 2 !== 0))) {
                    continue;
                }
                $add($type, max($chain));
            }
        }
        if (isset($by[4])) {
            $four = max($by[4]);
            $rest = array_diff_key($counts, [$four => 0]);
            if ($n === 6 && !(isset($rest[16], $rest[17]))) {
                $add('four2', $four);
            }
            if ($n === 8 && !isset($rest[16]) && !isset($rest[17]) && !array_filter($rest, fn($c) => $c % 2 !== 0)) {
                $add('four2pairs', $four);
            }
        }
        return $out;
    }

    /** True when $play may be put on $last. */
    public static function beats(array $play, array $last): bool
    {
        if ($play['type'] === 'rocket') {
            return true;
        }
        if ($last['type'] === 'rocket') {
            return false;
        }
        if ($play['type'] === 'bomb') {
            return $last['type'] !== 'bomb' || $play['rank'] > $last['rank'];
        }
        return $play['type'] === $last['type'] && $play['n'] === $last['n'] && $play['rank'] > $last['rank'];
    }

    /**
     * The play these cards make: following $last, a reading that beats it; leading, the most natural reading.
     * null when the cards are no play, or do not beat $last.
     */
    public static function read(array $cards, ?array $last): ?array
    {
        foreach (self::analyze($cards) as $play) {
            if ($last === null || self::beats($play, $last)) {
                return $play;
            }
        }
        return null;
    }

    /* ---------- computer player ---------- */

    /** value => the cards of that value in the hand */
    private static function groups(array $hand): array
    {
        $g = [];
        foreach ($hand as $c) {
            $g[self::value($c)][] = $c;
        }
        ksort($g);
        return $g;
    }

    /**
     * The plays a hand can make, built from its groups. Bombs and the rocket are always included; for wings
     * the lowest loose cards are used. ($withFours: also four-with-two, only used to finish a hand.)
     */
    public static function candidates(array $hand, bool $withFours = false): array
    {
        $g = self::groups($hand);
        $count = array_map('count', $g);
        $out = [];
        $take = fn(int $v, int $k) => array_slice($g[$v], 0, $k);

        foreach ($count as $v => $c) {
            $out[] = self::play('single', $v, $take($v, 1));
            if ($c >= 2) {
                $out[] = self::play('pair', $v, $take($v, 2));
            }
            if ($c >= 3) {
                $out[] = self::play('triple', $v, $take($v, 3));
            }
            if ($c === 4) {
                $out[] = self::play('bomb', $v, $g[$v]);
            }
        }
        if (isset($g[16], $g[17])) {
            $out[] = self::play('rocket', 17, ['LJ', 'BJ']);
        }

        // wings: the lowest values not used by the main part, single cards first
        $wings = function (array $skip, int $k, int $size) use ($count, $take) {
            $pool = array_filter($count, fn($c, $v) => !in_array($v, $skip, true) && $c >= $size && $c < 4 && !($size === 2 && $v >= 16), ARRAY_FILTER_USE_BOTH);
            uasort($pool, fn($a, $b) => $a <=> $b); // loose cards before cards of a pair or triple
            $pool = array_keys($pool);
            $picked = [];
            foreach ($pool as $v) {
                if (count($picked) === $k) {
                    break;
                }
                if ($size === 1 && in_array($v, [16, 17], true) && (in_array(16, $picked, true) || in_array(17, $picked, true))) {
                    continue; // not both jokers as wings
                }
                $picked[] = $v;
            }
            if (count($picked) < $k) {
                return null;
            }
            $cards = [];
            foreach ($picked as $v) {
                $cards = array_merge($cards, $take($v, $size));
            }
            return $cards;
        };

        foreach ($count as $v => $c) {
            if ($c >= 3) {
                foreach ([1 => 'triple1', 2 => 'triple2'] as $size => $type) {
                    $w = $wings([$v], 1, $size);
                    if ($w !== null) {
                        $out[] = self::play($type, $v, array_merge($take($v, 3), $w));
                    }
                }
            }
        }

        // rows: straights (5+), pairs (3+), planes (2+), with every length and start
        foreach ([[1, 5, 'straight'], [2, 3, 'pairs'], [3, 2, 'plane']] as [$size, $minLen, $type]) {
            for ($start = 3; $start <= self::TOP_IN_ROW; $start++) {
                $chain = [];
                for ($v = $start; $v <= self::TOP_IN_ROW && ($count[$v] ?? 0) >= $size; $v++) {
                    $chain[] = $v;
                    if (count($chain) < $minLen) {
                        continue;
                    }
                    $cards = [];
                    foreach ($chain as $x) {
                        $cards = array_merge($cards, $take($x, $size));
                    }
                    $out[] = self::play($type, $v, $cards);
                    if ($type === 'plane') {
                        foreach ([1 => 'plane1', 2 => 'plane2'] as $wsize => $wtype) {
                            $w = $wings($chain, count($chain), $wsize);
                            if ($w !== null) {
                                $out[] = self::play($wtype, $v, array_merge($cards, $w));
                            }
                        }
                    }
                }
            }
        }

        if ($withFours) {
            foreach ($count as $v => $c) {
                if ($c === 4) {
                    foreach ([1 => 'four2', 2 => 'four2pairs'] as $size => $type) {
                        $w = $wings([$v], 2, $size);
                        if ($w !== null) {
                            $out[] = self::play($type, $v, array_merge($g[$v], $w));
                        }
                    }
                }
            }
        }
        return $out;
    }

    /** How much a play costs the rest of the hand: breaking up bombs, the rocket, pairs and triples. */
    private static function cost(array $play, array $hand): float
    {
        $count = array_map('count', self::groups($hand));
        $used = array_map('count', self::groups($play['cards']));
        $cost = 0.0;
        foreach ($used as $v => $k) {
            $have = $count[$v] ?? 0;
            if ($have === 4 && !in_array($play['type'], ['bomb', 'four2', 'four2pairs'], true)) {
                $cost += 12; // a bomb broken up
            } elseif ($have > $k && !($play['type'] === 'triple1' && $k === 1) && !in_array($play['type'], ['triple2', 'plane2'], true)) {
                $cost += $have - $k === 1 ? 3 : 4; // a pair or triple broken up
            }
            if ($v >= 16 && $play['type'] !== 'rocket' && isset($count[16], $count[17])) {
                $cost += 10; // the rocket broken up
            }
        }
        return $cost;
    }

    /** How good a hand is for playing landlord (jokers, 2s, aces, bombs). */
    public static function strength(array $hand): int
    {
        $count = array_map('count', self::groups($hand));
        $score = ($count[17] ?? 0) * 4 + ($count[16] ?? 0) * 3 + ($count[15] ?? 0) * 2 + ($count[14] ?? 0);
        foreach ($count as $v => $c) {
            if ($c === 4 && $v < 15) {
                $score += 4;
            }
        }
        return $score;
    }

    /** The bid of the computer (1 to 3) or 0 to pass, given the highest bid so far. */
    public static function aiBid(array $hand, int $current): int
    {
        $s = self::strength($hand);
        $want = $s >= 11 ? 3 : ($s >= 8 ? 2 : ($s >= 6 ? 1 : 0));
        return $want > $current ? $want : 0;
    }

    /**
     * The move of the computer: the cards to play, or [] to pass.
     * $last: the play to beat (null when leading); $lastIsPartner: that play was made by the partner;
     * $nextCount / $opponentMin: cards left of the next player / the opponent with the fewest cards;
     * $nextIsOpponent: the next player is an opponent.
     */
    public static function aiMove(array $hand, ?array $last, bool $lastIsPartner, int $opponentMin, bool $nextIsOpponent, int $nextCount): array
    {
        // the whole hand in one go
        $all = self::read($hand, $last);
        if ($all !== null) {
            return $all['cards'];
        }
        $cands = self::candidates($hand, true);

        if ($last === null) {
            return self::aiLead($hand, $cands, $nextIsOpponent ? $nextCount : 99);
        }

        $beating = array_values(array_filter($cands, fn($p) => self::beats($p, $last)));
        if (!$beating) {
            return [];
        }
        // the partner leads the trick: let them, unless that wins the hand for us anyway
        if ($lastIsPartner) {
            foreach ($beating as $p) {
                if ($p['n'] === count($hand)) {
                    return $p['cards'];
                }
            }
            return [];
        }

        $normal = array_values(array_filter($beating, fn($p) => !in_array($p['type'], ['bomb', 'rocket'], true) || $p['type'] === $last['type']));
        $big = array_values(array_filter($beating, fn($p) => in_array($p['type'], ['bomb', 'rocket'], true) && $p['type'] !== $last['type']));
        usort($normal, fn($a, $b) => [self::cost($a, $hand) + $a['rank'] / 4, $a['rank']] <=> [self::cost($b, $hand) + $b['rank'] / 4, $b['rank']]);

        $danger = $opponentMin <= 2 || ($opponentMin <= 4 && $last['n'] >= 3);
        foreach ($normal as $p) {
            $cost = self::cost($p, $hand);
            // keep big cards when there is no hurry; at the end of the hand anything goes
            if ($danger || $cost < 8 || count($hand) <= 6) {
                return $p['cards'];
            }
        }
        // bombs: when the opponent is about to win, or when it nearly finishes our hand
        if ($big && ($danger || count($hand) - $big[0]['n'] <= 4)) {
            usort($big, fn($a, $b) => [$a['type'] === 'rocket', $a['rank']] <=> [$b['type'] === 'rocket', $b['rank']]);
            return $big[0]['cards'];
        }
        return [];
    }

    /** Leading: get rid of the longest run of low cards; keep 2s, jokers and bombs for later. */
    private static function aiLead(array $hand, array $cands, int $nextCount): array
    {
        $best = null;
        $bestScore = PHP_FLOAT_MAX;
        foreach ($cands as $p) {
            if (in_array($p['type'], ['bomb', 'rocket', 'four2', 'four2pairs'], true) && $p['n'] < count($hand)) {
                continue;
            }
            $score = $p['rank'] * 1.0 - $p['n'] * 1.6 + self::cost($p, $hand) * 1.5;
            if ($p['rank'] >= 15) {
                $score += 8; // 2s and jokers are for taking the lead back
            }
            // the next opponent has one card left: no single card, unless it is the highest we have
            if ($nextCount === 1 && $p['type'] === 'single') {
                $score += 30 - $p['rank'];
            }
            if ($nextCount === 2 && $p['type'] === 'pair') {
                $score += 20 - $p['rank'];
            }
            if ($score < $bestScore) {
                $best = $p;
                $bestScore = $score;
            }
        }
        if ($best === null) { // only bombs left
            $bombs = array_filter($cands, fn($p) => in_array($p['type'], ['bomb', 'rocket'], true));
            usort($bombs, fn($a, $b) => $a['rank'] <=> $b['rank']);
            $best = reset($bombs) ?: self::play('single', self::value(end($hand)), [end($hand)]);
        }
        return $best['cards'];
    }
}
