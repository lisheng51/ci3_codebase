<?php

/**
 * Xiangqi (中国象棋) rules: all piece moves (the horse's leg and the elephant's eye can be blocked, the cannon jumps
 * one piece to capture), the generals may never face each other on an open file, and a player without a legal move
 * loses (checkmate or stalemate). Draws: threefold repetition, 60 moves each without a capture, or no attacking
 * pieces left. (Simplified: the Asian rules on perpetual check and chasing are not applied; a repetition is a draw.)
 * Plus a computer player (alpha-beta).
 *
 * Position: ['b' => board, 'w' => red to move, 'h' => half moves since a capture, 'n' => move number].
 * The board is a 13x14 string with a border of two: row 0 (top, black's side) to row 9 (bottom, red), column 0..8;
 * index = (row + 2) * 13 + column + 2. '.' empty, ' ' off the board; red upper case, black lower case:
 * K general, A advisor, B elephant, N horse, R chariot, C cannon, P soldier.
 * A move is [from, to]; as text: columns a..i and ranks 0..9 from red's side, e.g. "h2e2" (cannon to the centre).
 */
class CasinoXiangqiRules
{
    public const START = 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR w - - 0 1';
    private const W = 13;
    private const ORTH = [-13, 13, -1, 1];
    private const DIAG = [-14, -12, 12, 14];
    /** Horse: the leg (one step straight) and the two squares it can reach over that leg. */
    private const HORSE = [[-13, [-27, -25]], [13, [25, 27]], [-1, [-15, 11]], [1, [-11, 15]]];
    private const VALUE = ['K' => 0, 'A' => 200, 'B' => 200, 'N' => 400, 'R' => 900, 'C' => 450, 'P' => 100];

    public static function start(): array
    {
        return self::fromFen(self::START);
    }

    public static function at(int $row, int $col): int
    {
        return ($row + 2) * self::W + $col + 2;
    }

    private static function row(int $i): int
    {
        return intdiv($i, self::W) - 2;
    }

    private static function col(int $i): int
    {
        return $i % self::W - 2;
    }

    public static function name(int $i): string
    {
        return chr(97 + self::col($i)) . (9 - self::row($i));
    }

    public static function idx(string $sq): int
    {
        return self::at(9 - (int)$sq[1], ord($sq[0]) - 97);
    }

    public static function fromFen(string $fen): array
    {
        $parts = explode(' ', trim($fen)) + [1 => 'w', 2 => '-', 3 => '-', 4 => '0', 5 => '1'];
        $b = str_repeat(' ', self::W * 14);
        foreach (explode('/', $parts[0]) as $r => $row) {
            $c = 0;
            foreach (str_split($row) as $ch) {
                if (ctype_digit($ch)) {
                    for ($k = 0; $k < (int)$ch; $k++) {
                        $b[self::at($r, $c++)] = '.';
                    }
                } else {
                    $b[self::at($r, $c++)] = $ch;
                }
            }
        }
        return ['b' => $b, 'w' => $parts[1] !== 'b', 'h' => (int)$parts[4], 'n' => (int)$parts[5]];
    }

    public static function toFen(array $pos): string
    {
        $rows = [];
        for ($r = 0; $r < 10; $r++) {
            $row = '';
            $empty = 0;
            for ($c = 0; $c < 9; $c++) {
                $p = $pos['b'][self::at($r, $c)];
                if ($p === '.') {
                    $empty++;
                    continue;
                }
                $row .= ($empty ?: '') . $p;
                $empty = 0;
            }
            $rows[] = $row . ($empty ?: '');
        }
        return implode('/', $rows) . ' ' . ($pos['w'] ? 'w' : 'b') . ' - - ' . $pos['h'] . ' ' . $pos['n'];
    }

    /** The board for the page: 10 rows from black's side (top) to red's side, '' for an empty point. */
    public static function grid(array $pos): array
    {
        $rows = [];
        for ($r = 0; $r < 10; $r++) {
            $row = [];
            for ($c = 0; $c < 9; $c++) {
                $p = $pos['b'][self::at($r, $c)];
                $row[] = $p === '.' ? '' : $p;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public static function key(array $pos): string
    {
        return $pos['b'] . ($pos['w'] ? 'w' : 'b');
    }

    public static function whiteToMove(array $pos): bool
    {
        return $pos['w'];
    }

    private static function inPalace(int $i, bool $red): bool
    {
        $r = self::row($i);
        $c = self::col($i);
        return $c >= 3 && $c <= 5 && ($red ? $r >= 7 && $r <= 9 : $r >= 0 && $r <= 2);
    }

    /** On its own side of the river. */
    private static function ownSide(int $i, bool $red): bool
    {
        $r = self::row($i);
        return $red ? $r >= 5 : $r <= 4;
    }

    /* ---------- moves ---------- */

    /** True when the general on $g is attacked by the red ($byRed) or black pieces, or faces the other general. */
    public static function attacked(string $b, int $g, bool $byRed): bool
    {
        [$K, $R, $C, $N, $P] = $byRed ? ['K', 'R', 'C', 'N', 'P'] : ['k', 'r', 'c', 'n', 'p'];
        foreach (self::ORTH as $d) {
            $t = $g + $d;
            while ($b[$t] === '.') {
                $t += $d;
            }
            if ($b[$t] === $R || ($b[$t] === $K && ($d === 13 || $d === -13))) {
                return true; // chariot, or the generals face each other
            }
            if ($b[$t] === ' ') {
                continue;
            }
            // over one piece (the screen): a cannon
            $t += $d;
            while ($b[$t] === '.') {
                $t += $d;
            }
            if ($b[$t] === $C) {
                return true;
            }
        }
        foreach (self::HORSE as [$leg, $targets]) {
            foreach ($targets as $to) {
                $h = $g - $to; // a horse there would jump to $g over its leg $h + $leg
                if ($b[$h] === $N && $b[$h + $leg] === '.') {
                    return true;
                }
            }
        }
        // soldiers: straight ahead, and sideways once over the river
        if ($byRed) {
            if ($b[$g + 13] === $P) {
                return true;
            }
            foreach ([-1, 1] as $d) {
                if ($b[$g + $d] === $P && self::row($g + $d) <= 4) {
                    return true;
                }
            }
        } else {
            if ($b[$g - 13] === $P) {
                return true;
            }
            foreach ([-1, 1] as $d) {
                if ($b[$g + $d] === $P && self::row($g + $d) >= 5) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function inCheck(array $pos): bool
    {
        $k = strpos($pos['b'], $pos['w'] ? 'K' : 'k');
        return $k !== false && self::attacked($pos['b'], $k, !$pos['w']);
    }

    private static function pseudo(array $pos, bool $captures = false): array
    {
        $b = $pos['b'];
        $red = $pos['w'];
        $own = $red ? fn($p) => $p >= 'A' && $p <= 'Z' : fn($p) => $p >= 'a' && $p <= 'z';
        $enemy = $red ? fn($p) => $p >= 'a' && $p <= 'z' : fn($p) => $p >= 'A' && $p <= 'Z';
        $ok = fn($t) => $b[$t] !== ' ' && (($b[$t] === '.' && !$captures) || $enemy($b[$t]));
        $moves = [];
        $len = strlen($b);
        for ($i = 0; $i < $len; $i++) {
            $p = $b[$i];
            if ($p === '.' || $p === ' ' || !$own($p)) {
                continue;
            }
            switch (strtoupper($p)) {
                case 'K':
                    foreach (self::ORTH as $d) {
                        if (self::inPalace($i + $d, $red) && $ok($i + $d)) {
                            $moves[] = [$i, $i + $d];
                        }
                    }
                    break;
                case 'A':
                    foreach (self::DIAG as $d) {
                        if (self::inPalace($i + $d, $red) && $ok($i + $d)) {
                            $moves[] = [$i, $i + $d];
                        }
                    }
                    break;
                case 'B':
                    foreach (self::DIAG as $d) {
                        $t = $i + 2 * $d;
                        if ($b[$i + $d] === '.' && $b[$t] !== ' ' && self::ownSide($t, $red) && $ok($t)) {
                            $moves[] = [$i, $t];
                        }
                    }
                    break;
                case 'N':
                    foreach (self::HORSE as [$leg, $targets]) {
                        if ($b[$i + $leg] !== '.') {
                            continue; // the leg is blocked
                        }
                        foreach ($targets as $to) {
                            if ($ok($i + $to)) {
                                $moves[] = [$i, $i + $to];
                            }
                        }
                    }
                    break;
                case 'R':
                    foreach (self::ORTH as $d) {
                        for ($t = $i + $d; $b[$t] === '.'; $t += $d) {
                            if (!$captures) {
                                $moves[] = [$i, $t];
                            }
                        }
                        if ($enemy($b[$t])) {
                            $moves[] = [$i, $t];
                        }
                    }
                    break;
                case 'C':
                    foreach (self::ORTH as $d) {
                        for ($t = $i + $d; $b[$t] === '.'; $t += $d) {
                            if (!$captures) {
                                $moves[] = [$i, $t];
                            }
                        }
                        if ($b[$t] === ' ') {
                            continue;
                        }
                        for ($t += $d; $b[$t] === '.'; $t += $d);
                        if ($enemy($b[$t])) {
                            $moves[] = [$i, $t];
                        }
                    }
                    break;
                case 'P':
                    $fwd = $red ? -13 : 13;
                    if ($ok($i + $fwd)) {
                        $moves[] = [$i, $i + $fwd];
                    }
                    if (!self::ownSide($i, $red)) {
                        foreach ([-1, 1] as $d) {
                            if ($ok($i + $d)) {
                                $moves[] = [$i, $i + $d];
                            }
                        }
                    }
                    break;
            }
        }
        return $moves;
    }

    public static function make(array $pos, array $m): array
    {
        [$f, $t] = $m;
        $b = $pos['b'];
        $cap = $b[$t];
        $b[$t] = $b[$f];
        $b[$f] = '.';
        return ['b' => $b, 'w' => !$pos['w'], 'h' => $cap !== '.' ? 0 : $pos['h'] + 1, 'n' => $pos['n'] + ($pos['w'] ? 0 : 1)];
    }

    private static function safe(array $pos, array $m): array|false
    {
        $next = self::make($pos, $m);
        $k = strpos($next['b'], $pos['w'] ? 'K' : 'k');
        return $k !== false && !self::attacked($next['b'], $k, !$pos['w']) ? $next : false;
    }

    public static function legal(array $pos): array
    {
        $out = [];
        foreach (self::pseudo($pos) as $m) {
            if (self::safe($pos, $m) !== false) {
                $out[] = $m;
            }
        }
        return $out;
    }

    public static function moveText(array $m): string
    {
        return self::name($m[0]) . self::name($m[1]);
    }

    public static function parse(array $pos, string $text): ?array
    {
        $text = strtolower(trim($text));
        if (!preg_match('/^([a-i][0-9])([a-i][0-9])$/', $text, $mm)) {
            return null;
        }
        $f = self::idx($mm[1]);
        $t = self::idx($mm[2]);
        foreach (self::legal($pos) as $m) {
            if ($m[0] === $f && $m[1] === $t) {
                return $m;
            }
        }
        return null;
    }

    /** null while the game goes on, else ['result' => 'white' (red) / 'black' / 'draw', 'reason']. */
    public static function outcome(array $pos, array $seen): ?array
    {
        if (!self::legal($pos)) {
            // in xiangqi a player who cannot move loses, also when not in check
            return ['result' => $pos['w'] ? 'black' : 'white', 'reason' => self::inCheck($pos) ? 'checkmate' : 'stalemate_loss'];
        }
        if (($seen[self::key($pos)] ?? 0) >= 3) {
            return ['result' => 'draw', 'reason' => 'repetition'];
        }
        if ($pos['h'] >= 120) {
            return ['result' => 'draw', 'reason' => 'sixty'];
        }
        if (!preg_match('/[RNCPrncp]/', $pos['b'])) {
            return ['result' => 'draw', 'reason' => 'material'];
        }
        return null;
    }

    /* ---------- computer player ---------- */

    /** Bonus per point for horses, cannons and soldiers (red's view, row 0 = black's back row). */
    private static function bonus(string $u, int $r, int $c): int
    {
        $centre = 4 - abs($c - 4);
        switch ($u) {
            case 'P':
                return $r <= 4 ? 60 + (4 - $r) * 10 + $centre * 8 - ($r === 0 ? 40 : 0) : ($r === 5 ? 10 : 0);
            case 'N':
                return $centre * 6 + ($r <= 4 ? 25 : 0) - ($r === 9 ? 20 : 0);
            case 'C':
                return $c === 4 ? 25 : $centre * 3;
            case 'R':
                return $r <= 4 ? 20 : 0;
        }
        return 0;
    }

    public static function evaluate(array $pos): int
    {
        $b = $pos['b'];
        $score = 0;
        $len = strlen($b);
        for ($i = 0; $i < $len; $i++) {
            $p = $b[$i];
            if ($p === '.' || $p === ' ') {
                continue;
            }
            $r = self::row($i);
            $c = self::col($i);
            if ($p >= 'A' && $p <= 'Z') {
                $score += self::VALUE[$p] + self::bonus($p, $r, $c);
            } else {
                $u = strtoupper($p);
                $score -= self::VALUE[$u] + self::bonus($u, 9 - $r, $c);
            }
        }
        return $pos['w'] ? $score : -$score;
    }

    private static float $deadline = 0;
    private static int $nodes = 0;
    private static bool $stop = false;
    private const MATE = 100000;

    private static function order(array $pos, array $moves): array
    {
        $b = $pos['b'];
        $scored = [];
        foreach ($moves as $m) {
            $victim = $b[$m[1]];
            $scored[] = [$victim !== '.' ? 10 * (self::VALUE[strtoupper($victim)] ?: 5000) - self::VALUE[strtoupper($b[$m[0]])] + 1000 : 0, $m];
        }
        usort($scored, fn($x, $y) => $y[0] <=> $x[0]);
        return array_column($scored, 1);
    }

    private static function quiesce(array $pos, int $alpha, int $beta, int $depth): int
    {
        $stand = self::evaluate($pos);
        if ($stand >= $beta) {
            return $beta;
        }
        $alpha = max($alpha, $stand);
        if ($depth >= 5) {
            return $alpha;
        }
        foreach (self::order($pos, self::pseudo($pos, true)) as $m) {
            $next = self::safe($pos, $m);
            if ($next === false) {
                continue;
            }
            $score = -self::quiesce($next, -$beta, -$alpha, $depth + 1);
            if ($score >= $beta) {
                return $beta;
            }
            $alpha = max($alpha, $score);
        }
        return $alpha;
    }

    private static function search(array $pos, int $depth, int $alpha, int $beta, int $ply): int
    {
        if ((++self::$nodes & 511) === 0 && microtime(true) > self::$deadline) {
            self::$stop = true;
        }
        if (self::$stop) {
            return 0;
        }
        if ($depth <= 0) {
            return self::quiesce($pos, $alpha, $beta, 0);
        }
        $any = false;
        foreach (self::order($pos, self::pseudo($pos)) as $m) {
            $next = self::safe($pos, $m);
            if ($next === false) {
                continue;
            }
            $any = true;
            $score = -self::search($next, $depth - 1, -$beta, -$alpha, $ply + 1);
            if (self::$stop) {
                return 0;
            }
            if ($score >= $beta) {
                return $beta;
            }
            $alpha = max($alpha, $score);
        }
        return $any ? $alpha : -self::MATE + $ply; // no move: lost (also without check)
    }

    /** The computer's move; $level 1 easy, 2 normal, 3 hard. */
    public static function bestMove(array $pos, int $level): ?array
    {
        $moves = self::legal($pos);
        if (!$moves) {
            return null;
        }
        [$maxDepth, $seconds, $noise] = [[1, 0.3, 60], [3, 0.8, 12], [6, 1.6, 0]][max(1, min(3, $level)) - 1];
        self::$deadline = microtime(true) + $seconds;
        self::$nodes = 0;
        self::$stop = false;
        $moves = self::order($pos, $moves);
        $best = $moves[0];
        for ($depth = 1; $depth <= $maxDepth; $depth++) {
            $alpha = -self::MATE - 1;
            $bestNow = null;
            $scores = [];
            foreach ($moves as $m) {
                $score = -self::search(self::make($pos, $m), $depth - 1, -self::MATE - 1, self::MATE + 1, 1) + ($noise ? random_int(0, $noise) : 0);
                if (self::$stop) {
                    break;
                }
                $scores[] = [$score, $m];
                if ($score > $alpha) {
                    $alpha = $score;
                    $bestNow = $m;
                }
            }
            if (self::$stop) {
                break;
            }
            $best = $bestNow ?? $best;
            usort($scores, fn($x, $y) => $y[0] <=> $x[0]);
            $moves = array_column($scores, 1);
            if ($alpha >= self::MATE - 100) {
                break;
            }
        }
        return $best;
    }

    public static function perft(array $pos, int $depth): int
    {
        if ($depth === 0) {
            return 1;
        }
        $n = 0;
        foreach (self::legal($pos) as $m) {
            $n += $depth === 1 ? 1 : self::perft(self::make($pos, $m), $depth - 1);
        }
        return $n;
    }
}
