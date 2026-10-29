<?php

/**
 * Chess rules (FIDE): all moves including castling, en passant and promotion; check, checkmate, stalemate and the
 * draws by the 50-move rule, threefold repetition and too little material. Plus a computer player (alpha-beta).
 *
 * Position: ['b' => board, 'w' => white to move, 'c' => castling rights ("KQkq"), 'e' => en passant square (0 = none),
 * 'h' => half moves since a capture or pawn move, 'n' => move number].
 * The board is a 10x12 string (a mailbox): a8 = 21 ... h8 = 28, a1 = 91 ... h1 = 98; '.' empty, ' ' off the board,
 * white pieces upper case (PNBRQK), black lower case.
 * A move is [from, to, promotion piece ('' or q/r/b/n)]; as text "e2e4", "e7e8q".
 */
class CasinoChessRules
{
    public const START = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
    private const KNIGHT = [-21, -19, -12, -8, 8, 12, 19, 21];
    private const KING = [-11, -10, -9, -1, 1, 9, 10, 11];
    private const DIAG = [-11, -9, 9, 11];
    private const ORTH = [-10, -1, 1, 10];
    private const VALUE = ['P' => 100, 'N' => 320, 'B' => 330, 'R' => 500, 'Q' => 900, 'K' => 0];
    /** Castling rights lost when a piece moves from or to this square. */
    private const RIGHTS = [95 => 'KQ', 98 => 'K', 91 => 'Q', 25 => 'kq', 28 => 'k', 21 => 'q'];

    /* ---------- position ---------- */

    public static function start(): array
    {
        return self::fromFen(self::START);
    }

    public static function idx(string $sq): int
    {
        return 21 + (ord($sq[0]) - 97) + (8 - (int)$sq[1]) * 10;
    }

    public static function name(int $i): string
    {
        return chr(96 + $i % 10) . (10 - intdiv($i, 10));
    }

    public static function fromFen(string $fen): array
    {
        $parts = explode(' ', trim($fen)) + [1 => 'w', 2 => '-', 3 => '-', 4 => '0', 5 => '1'];
        $b = str_repeat(' ', 120);
        foreach (explode('/', $parts[0]) as $r => $row) {
            $f = 0;
            foreach (str_split($row) as $ch) {
                if (ctype_digit($ch)) {
                    for ($k = 0; $k < (int)$ch; $k++) {
                        $b[21 + $r * 10 + $f++] = '.';
                    }
                } else {
                    $b[21 + $r * 10 + $f++] = $ch;
                }
            }
        }
        return ['b' => $b, 'w' => $parts[1] === 'w', 'c' => $parts[2] === '-' ? '' : $parts[2],
            'e' => $parts[3] === '-' ? 0 : self::idx($parts[3]), 'h' => (int)$parts[4], 'n' => (int)$parts[5]];
    }

    public static function toFen(array $pos): string
    {
        $rows = [];
        for ($r = 0; $r < 8; $r++) {
            $row = '';
            $empty = 0;
            for ($f = 0; $f < 8; $f++) {
                $p = $pos['b'][21 + $r * 10 + $f];
                if ($p === '.') {
                    $empty++;
                    continue;
                }
                $row .= ($empty ?: '') . $p;
                $empty = 0;
            }
            $rows[] = $row . ($empty ?: '');
        }
        return implode('/', $rows) . ' ' . ($pos['w'] ? 'w' : 'b') . ' ' . ($pos['c'] ?: '-') . ' '
            . ($pos['e'] ? self::name($pos['e']) : '-') . ' ' . $pos['h'] . ' ' . $pos['n'];
    }

    /** The board for the page: 8 rows from rank 8 to rank 1, '' for an empty square. */
    public static function grid(array $pos): array
    {
        $rows = [];
        for ($r = 0; $r < 8; $r++) {
            $row = [];
            for ($f = 0; $f < 8; $f++) {
                $p = $pos['b'][21 + $r * 10 + $f];
                $row[] = $p === '.' ? '' : $p;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /** For threefold repetition: the same pieces, side to move, castling rights and en passant square. */
    public static function key(array $pos): string
    {
        return $pos['b'] . ($pos['w'] ? 'w' : 'b') . $pos['c'] . $pos['e'];
    }

    public static function whiteToMove(array $pos): bool
    {
        return $pos['w'];
    }

    private static function isWhite(string $p): bool
    {
        return $p >= 'A' && $p <= 'Z';
    }

    private static function isBlack(string $p): bool
    {
        return $p >= 'a' && $p <= 'z';
    }

    /* ---------- moves ---------- */

    /** True when $sq is attacked by the white ($byWhite) or black pieces. */
    public static function attacked(string $b, int $sq, bool $byWhite): bool
    {
        [$P, $N, $B, $R, $Q, $K] = $byWhite ? ['P', 'N', 'B', 'R', 'Q', 'K'] : ['p', 'n', 'b', 'r', 'q', 'k'];
        if ($byWhite ? ($b[$sq + 9] === $P || $b[$sq + 11] === $P) : ($b[$sq - 9] === $P || $b[$sq - 11] === $P)) {
            return true;
        }
        foreach (self::KNIGHT as $d) {
            if ($b[$sq + $d] === $N) {
                return true;
            }
        }
        foreach (self::KING as $d) {
            if ($b[$sq + $d] === $K) {
                return true;
            }
        }
        foreach (self::DIAG as $d) {
            for ($t = $sq + $d; $b[$t] === '.'; $t += $d);
            if ($b[$t] === $B || $b[$t] === $Q) {
                return true;
            }
        }
        foreach (self::ORTH as $d) {
            for ($t = $sq + $d; $b[$t] === '.'; $t += $d);
            if ($b[$t] === $R || $b[$t] === $Q) {
                return true;
            }
        }
        return false;
    }

    public static function inCheck(array $pos): bool
    {
        $k = strpos($pos['b'], $pos['w'] ? 'K' : 'k');
        return $k !== false && self::attacked($pos['b'], $k, !$pos['w']);
    }

    /** Moves that follow the way the pieces move, without looking at check yet. ($captures: only captures and promotions) */
    private static function pseudo(array $pos, bool $captures = false): array
    {
        $b = $pos['b'];
        $white = $pos['w'];
        $own = $white ? fn($p) => $p >= 'A' && $p <= 'Z' : fn($p) => $p >= 'a' && $p <= 'z';
        $enemy = $white ? fn($p) => $p >= 'a' && $p <= 'z' : fn($p) => $p >= 'A' && $p <= 'Z';
        $moves = [];
        for ($i = 21; $i <= 98; $i++) {
            $p = $b[$i];
            if ($p === '.' || $p === ' ' || !$own($p)) {
                continue;
            }
            switch (strtoupper($p)) {
                case 'P':
                    $dir = $white ? -10 : 10;
                    $last = $white ? ($i + $dir <= 28) : ($i + $dir >= 91);
                    $add = function ($t) use (&$moves, $i, $last) {
                        if ($last) {
                            foreach (['q', 'r', 'b', 'n'] as $pr) {
                                $moves[] = [$i, $t, $pr];
                            }
                        } else {
                            $moves[] = [$i, $t, ''];
                        }
                    };
                    $one = $i + $dir;
                    if ($b[$one] === '.' && (!$captures || $last)) {
                        $add($one);
                        $startRow = $white ? ($i >= 81 && $i <= 88) : ($i >= 31 && $i <= 38);
                        if ($startRow && $b[$one + $dir] === '.' && !$captures) {
                            $moves[] = [$i, $one + $dir, ''];
                        }
                    }
                    foreach ([$dir - 1, $dir + 1] as $d) {
                        $t = $i + $d;
                        if ($enemy($b[$t]) || ($t === $pos['e'] && $pos['e'] > 0)) {
                            $add($t);
                        }
                    }
                    break;
                case 'N':
                case 'K':
                    foreach (strtoupper($p) === 'N' ? self::KNIGHT : self::KING as $d) {
                        $t = $i + $d;
                        $q = $b[$t];
                        if (($q === '.' && !$captures) || $enemy($q)) {
                            $moves[] = [$i, $t, ''];
                        }
                    }
                    if (strtoupper($p) === 'K' && !$captures) {
                        foreach (self::castles($pos, $i) as $t) {
                            $moves[] = [$i, $t, ''];
                        }
                    }
                    break;
                default: // B, R, Q
                    $u = strtoupper($p);
                    $dirs = $u === 'B' ? self::DIAG : ($u === 'R' ? self::ORTH : self::KING);
                    foreach ($dirs as $d) {
                        for ($t = $i + $d; $b[$t] === '.'; $t += $d) {
                            if (!$captures) {
                                $moves[] = [$i, $t, ''];
                            }
                        }
                        if ($enemy($b[$t])) {
                            $moves[] = [$i, $t, ''];
                        }
                    }
            }
        }
        return $moves;
    }

    /** Castling targets of the king on $k: the squares between are empty and the king does not pass through check. */
    private static function castles(array $pos, int $k): array
    {
        $b = $pos['b'];
        $white = $pos['w'];
        if ($k !== ($white ? 95 : 25) || $pos['c'] === '') {
            return [];
        }
        $out = [];
        $enemy = !$white;
        [$kc, $qc, $rook] = $white ? ['K', 'Q', 'R'] : ['k', 'q', 'r'];
        if (str_contains($pos['c'], $kc) && $b[$k + 1] === '.' && $b[$k + 2] === '.' && $b[$k + 3] === $rook
            && !self::attacked($b, $k, $enemy) && !self::attacked($b, $k + 1, $enemy)) {
            $out[] = $k + 2;
        }
        if (str_contains($pos['c'], $qc) && $b[$k - 1] === '.' && $b[$k - 2] === '.' && $b[$k - 3] === '.' && $b[$k - 4] === $rook
            && !self::attacked($b, $k, $enemy) && !self::attacked($b, $k - 1, $enemy)) {
            $out[] = $k - 2;
        }
        return $out;
    }

    public static function make(array $pos, array $m): array
    {
        [$f, $t, $pr] = $m;
        $b = $pos['b'];
        $p = $b[$f];
        $cap = $b[$t];
        $white = $pos['w'];
        $u = strtoupper($p);
        $b[$t] = $pr !== '' ? ($white ? strtoupper($pr) : $pr) : $p;
        $b[$f] = '.';
        $ep = 0;
        if ($u === 'P') {
            if ($t === $pos['e'] && $cap === '.') {
                $b[$t + ($white ? 10 : -10)] = '.'; // en passant
            }
            if (abs($t - $f) === 20) {
                $ep = intdiv($f + $t, 2);
            }
        } elseif ($u === 'K' && abs($t - $f) === 2) {
            [$rf, $rt] = $t > $f ? [$f + 3, $f + 1] : [$f - 4, $f - 1];
            $b[$rt] = $b[$rf];
            $b[$rf] = '.';
        }
        $c = $pos['c'];
        if ($c !== '') {
            foreach ([$f, $t] as $sq) {
                if (isset(self::RIGHTS[$sq])) {
                    $c = str_replace(str_split(self::RIGHTS[$sq]), '', $c);
                }
            }
        }
        return ['b' => $b, 'w' => !$white, 'c' => $c, 'e' => $ep,
            'h' => $u === 'P' || $cap !== '.' ? 0 : $pos['h'] + 1, 'n' => $pos['n'] + ($white ? 0 : 1)];
    }

    /** True when the move does not leave the own king in check. */
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
        return self::name($m[0]) . self::name($m[1]) . $m[2];
    }

    /** The legal move written as $text ("e2e4", "e7e8q"; a promotion without a piece becomes a queen), or null. */
    public static function parse(array $pos, string $text): ?array
    {
        $text = strtolower(trim($text));
        if (!preg_match('/^([a-h][1-8])([a-h][1-8])([qrbn]?)$/', $text, $mm)) {
            return null;
        }
        $f = self::idx($mm[1]);
        $t = self::idx($mm[2]);
        foreach (self::legal($pos) as $m) {
            if ($m[0] === $f && $m[1] === $t && ($m[2] === $mm[3] || ($mm[3] === '' && $m[2] === 'q'))) {
                return $m;
            }
        }
        return null;
    }

    /**
     * How the game stands: null while it goes on, else ['result' => 'white' / 'black' / 'draw', 'reason'].
     * $seen: position key => how often it was on the board.
     */
    public static function outcome(array $pos, array $seen): ?array
    {
        if (!self::legal($pos)) {
            return self::inCheck($pos)
                ? ['result' => $pos['w'] ? 'black' : 'white', 'reason' => 'checkmate']
                : ['result' => 'draw', 'reason' => 'stalemate'];
        }
        if ($pos['h'] >= 100) {
            return ['result' => 'draw', 'reason' => 'fifty'];
        }
        if (($seen[self::key($pos)] ?? 0) >= 3) {
            return ['result' => 'draw', 'reason' => 'repetition'];
        }
        if (self::insufficient($pos['b'])) {
            return ['result' => 'draw', 'reason' => 'material'];
        }
        return null;
    }

    /** Kings only, or a king and one bishop or knight against a lone king. */
    private static function insufficient(string $b): bool
    {
        $rest = str_replace(['.', ' ', 'K', 'k'], '', $b);
        return $rest === '' || (strlen($rest) === 1 && in_array(strtoupper($rest), ['B', 'N'], true));
    }

    /* ---------- computer player ---------- */

    /** Square tables (white's view, a8 first): where a piece stands well. */
    private const PST = [
        'P' => [0, 0, 0, 0, 0, 0, 0, 0, 50, 50, 50, 50, 50, 50, 50, 50, 10, 10, 20, 30, 30, 20, 10, 10, 5, 5, 10, 25, 25, 10, 5, 5, 0, 0, 0, 20, 20, 0, 0, 0, 5, -5, -10, 0, 0, -10, -5, 5, 5, 10, 10, -20, -20, 10, 10, 5, 0, 0, 0, 0, 0, 0, 0, 0],
        'N' => [-50, -40, -30, -30, -30, -30, -40, -50, -40, -20, 0, 0, 0, 0, -20, -40, -30, 0, 10, 15, 15, 10, 0, -30, -30, 5, 15, 20, 20, 15, 5, -30, -30, 0, 15, 20, 20, 15, 0, -30, -30, 5, 10, 15, 15, 10, 5, -30, -40, -20, 0, 5, 5, 0, -20, -40, -50, -40, -30, -30, -30, -30, -40, -50],
        'B' => [-20, -10, -10, -10, -10, -10, -10, -20, -10, 0, 0, 0, 0, 0, 0, -10, -10, 0, 5, 10, 10, 5, 0, -10, -10, 5, 5, 10, 10, 5, 5, -10, -10, 0, 10, 10, 10, 10, 0, -10, -10, 10, 10, 10, 10, 10, 10, -10, -10, 5, 0, 0, 0, 0, 5, -10, -20, -10, -10, -10, -10, -10, -10, -20],
        'R' => [0, 0, 0, 0, 0, 0, 0, 0, 5, 10, 10, 10, 10, 10, 10, 5, -5, 0, 0, 0, 0, 0, 0, -5, -5, 0, 0, 0, 0, 0, 0, -5, -5, 0, 0, 0, 0, 0, 0, -5, -5, 0, 0, 0, 0, 0, 0, -5, -5, 0, 0, 0, 0, 0, 0, -5, 0, 0, 0, 5, 5, 0, 0, 0],
        'Q' => [-20, -10, -10, -5, -5, -10, -10, -20, -10, 0, 0, 0, 0, 0, 0, -10, -10, 0, 5, 5, 5, 5, 0, -10, -5, 0, 5, 5, 5, 5, 0, -5, 0, 0, 5, 5, 5, 5, 0, -5, -10, 5, 5, 5, 5, 5, 0, -10, -10, 0, 5, 0, 0, 0, 0, -10, -20, -10, -10, -5, -5, -10, -10, -20],
        'K' => [-30, -40, -40, -50, -50, -40, -40, -30, -30, -40, -40, -50, -50, -40, -40, -30, -30, -40, -40, -50, -50, -40, -40, -30, -30, -40, -40, -50, -50, -40, -40, -30, -20, -30, -30, -40, -40, -30, -30, -20, -10, -20, -20, -20, -20, -20, -20, -10, 20, 20, 0, 0, 0, 0, 20, 20, 20, 30, 10, 0, 0, 10, 30, 20],
    ];

    /** Score for the side to move (centipawns). */
    public static function evaluate(array $pos): int
    {
        $b = $pos['b'];
        $score = 0;
        for ($i = 21; $i <= 98; $i++) {
            $p = $b[$i];
            if ($p === '.' || $p === ' ') {
                continue;
            }
            $r = intdiv($i, 10) - 2;
            $f = $i % 10 - 1;
            if (self::isWhite($p)) {
                $score += self::VALUE[$p] + self::PST[$p][$r * 8 + $f];
            } else {
                $u = strtoupper($p);
                $score -= self::VALUE[$u] + self::PST[$u][(7 - $r) * 8 + $f];
            }
        }
        return $pos['w'] ? $score : -$score;
    }

    private static float $deadline = 0;
    private static int $nodes = 0;
    private static bool $stop = false;
    private const MATE = 100000;

    /** Captures first, the most valuable victim by the least valuable attacker. */
    private static function order(array $pos, array $moves): array
    {
        $b = $pos['b'];
        $scored = [];
        foreach ($moves as $m) {
            $victim = $b[$m[1]];
            $s = $victim !== '.' ? 10 * self::VALUE[strtoupper($victim)] - self::VALUE[strtoupper($b[$m[0]])] + 1000 : 0;
            if ($m[2] === 'q') {
                $s += 800;
            }
            $scored[] = [$s, $m];
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
        if ($depth >= 6) {
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
        if (!$any) {
            return self::inCheck($pos) ? -self::MATE + $ply : 0;
        }
        return $alpha;
    }

    /**
     * The computer's move. $level: 1 easy (one move deep, with chance), 2 normal, 3 hard (deeper, more time).
     * Searches deeper as long as the time allows and plays the best move of the last finished depth.
     */
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
            // the next depth looks at the best moves first
            usort($scores, fn($x, $y) => $y[0] <=> $x[0]);
            $moves = array_column($scores, 1);
            if ($alpha >= self::MATE - 100) {
                break; // a mate is found
            }
        }
        return $best;
    }

    /** Counts the positions $depth moves deep (to check the move generator against known numbers). */
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
