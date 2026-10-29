<?php

/**
 * Casino settings, stored in the core config table (Back > Casino > Instelling).
 * Every key has a default, so the games also run before anything is saved.
 */
class CasinoConfigModel
{
    public const DEFAULTS = [
        'casino_google_client_id' => '',
        'casino_card_login' => '1',
        'casino_card_register_per_hour' => '5',
        'casino_start_balance' => '1000',
        'casino_daily_bonus' => '200',
        'casino_poll_ms' => '1500',
        'casino_idle_seconds' => '90',

        'casino_dice_bet_seconds' => '20',
        'casino_dice_result_seconds' => '8',
        'casino_dice_payout_bigsmall' => '1',
        'casino_dice_payout_oddeven' => '1',
        'casino_dice_payout_triple' => '31',
        'casino_dice_payout_double' => '12',
        'casino_dice_payout_specific_triple' => '190',
        'casino_dice_payout_combo' => '6',
        'casino_dice_payout_single2' => '2',
        'casino_dice_payout_single3' => '12',
        'casino_dice_payout_totals' => '4:62,5:31,6:18,7:12,8:8,9:7,10:6,11:6,12:7,13:8,14:12,15:18,16:31,17:62',

        'casino_roulette_bet_seconds' => '20',
        'casino_roulette_result_seconds' => '14',

        'casino_bj_decks' => '6',
        'casino_bj_payout' => '1.5',
        'casino_bj_dealer_hits_soft17' => '0',
        'casino_bj_allow_double' => '1',
        'casino_bj_bet_seconds' => '15',
        'casino_bj_turn_seconds' => '20',
        'casino_bj_result_seconds' => '8',

        'casino_slot_paytable' => 'seven:300,bar:100,bell:50,plum:25,orange:20,lemon:15,cherry:10,cherry2:3,cherry1:1',
        'casino_slot_jackpot_seed' => '1000',
        'casino_slot_jackpot_percent' => '2',

        'casino_poker_turn_seconds' => '20',
        'casino_poker_result_seconds' => '7',
        'casino_poker_min_buyin_bb' => '20',
        'casino_poker_rake_percent' => '0',

        'casino_bingo_buy_seconds' => '30',
        'casino_bingo_draw_seconds' => '2',
        'casino_bingo_result_seconds' => '10',
        'casino_bingo_payout_percent' => '90',
        'casino_bingo_line_percent' => '25',

        'casino_ddz_min_buyin' => '50',
        'casino_ddz_max_mult' => '64',
        'casino_ddz_turn_seconds' => '30',
        'casino_ddz_result_seconds' => '6',
        'casino_ddz_bomb_mult' => '2',
        'casino_ddz_rocket_mult' => '4',

        'casino_board_minutes' => '10',
        'casino_board_increment' => '5',
        'casino_board_away_seconds' => '60',
        'casino_board_result_seconds' => '10',
        'casino_board_solo_win_easy' => '20',
        'casino_board_solo_win_normal' => '60',
        'casino_board_solo_win_hard' => '100',
    ];

    /** Labels for the settings page, grouped per tab. */
    public const LABELS = [
        'Algemeen' => [
            'casino_google_client_id' => 'Google Client ID (inloggen)',
            'casino_card_login' => 'Inloggen met Holland Casino kaart scannen (1 = aan, 0 = uit)',
            'casino_card_register_per_hour' => 'Nieuwe kaartspelers per IP per uur (0 = geen limiet)',
            'casino_start_balance' => 'Startfiches voor een nieuwe speler',
            'casino_daily_bonus' => 'Dagelijkse bonus (0 = uit)',
            'casino_poll_ms' => 'Verversen tafel (milliseconden)',
            'casino_idle_seconds' => 'Speler van tafel na (seconden zonder verbinding)',
        ],
        'Dobbelstenen' => [
            'casino_dice_bet_seconds' => 'Inzettijd per ronde (seconden)',
            'casino_dice_result_seconds' => 'Uitslag tonen (seconden)',
            'casino_dice_payout_bigsmall' => 'Uitbetaling groot / klein (x : 1)',
            'casino_dice_payout_oddeven' => 'Uitbetaling oneven / even (x : 1)',
            'casino_dice_payout_triple' => 'Uitbetaling willekeurige triple (x : 1)',
            'casino_dice_payout_specific_triple' => 'Uitbetaling bepaalde triple, bv. drie keer 5 (x : 1)',
            'casino_dice_payout_double' => 'Uitbetaling bepaald dubbel, bv. minstens twee keer 3 (x : 1)',
            'casino_dice_payout_combo' => 'Uitbetaling combinatie van twee getallen, bv. 2 en 5 (x : 1)',
            'casino_dice_payout_single2' => 'Enkel getal op 2 dobbelstenen (x : 1; op 1 steen altijd 1 : 1)',
            'casino_dice_payout_single3' => 'Enkel getal op alle 3 dobbelstenen (x : 1)',
            'casino_dice_payout_totals' => 'Uitbetaling totaal (totaal:x, ...)',
        ],
        'Roulette' => [
            'casino_roulette_bet_seconds' => 'Inzettijd per ronde (seconden)',
            'casino_roulette_result_seconds' => 'Uitslag tonen, inclusief draaien van het wiel (seconden, minimaal 12 aanbevolen)',
        ],
        'Blackjack' => [
            'casino_bj_decks' => 'Aantal kaartspellen in de schoen (1-8)',
            'casino_bj_payout' => 'Uitbetaling blackjack (1.5 = 3:2, 1.2 = 6:5)',
            'casino_bj_dealer_hits_soft17' => 'Bank pakt bij soft 17 (1 = ja, 0 = nee)',
            'casino_bj_allow_double' => 'Verdubbelen toegestaan (1 = ja, 0 = nee)',
            'casino_bj_bet_seconds' => 'Inzettijd (seconden)',
            'casino_bj_turn_seconds' => 'Bedenktijd per beurt (seconden)',
            'casino_bj_result_seconds' => 'Uitslag tonen (seconden)',
        ],
        'Fruitautomaat' => [
            'casino_slot_paytable' => 'Uitbetaling per lijn x inzet per lijn (symbool:x; cherry2 = 2 kersen links, cherry1 = 1 kers links)',
            'casino_slot_jackpot_seed' => 'Jackpot begint met (fiches van het huis)',
            'casino_slot_jackpot_percent' => 'Deel van elke inzet naar de jackpot (procent)',
        ],
        'Poker' => [
            'casino_poker_turn_seconds' => 'Bedenktijd per beurt (seconden; daarna check of fold)',
            'casino_poker_result_seconds' => 'Uitslag tonen voor de volgende hand (seconden)',
            'casino_poker_min_buyin_bb' => 'Minimale inleg in big blinds',
            'casino_poker_rake_percent' => 'Rake van elke pot na de flop (procent, 0 = geen)',
        ],
        'Bingo' => [
            'casino_bingo_buy_seconds' => 'Tijd om kaarten te kopen (seconden, start bij de eerste kaart)',
            'casino_bingo_draw_seconds' => 'Seconden tussen twee ballen',
            'casino_bingo_result_seconds' => 'Uitslag tonen (seconden)',
            'casino_bingo_payout_percent' => 'Prijzenpot: procent van de verkochte kaarten',
            'casino_bingo_line_percent' => 'Deel van de pot voor de eerste lijn (procent; de rest is voor de volle kaart)',
        ],
        'Dou Dizhu' => [
            'casino_ddz_min_buyin' => 'Minimale inleg in basisinzetten',
            'casino_ddz_bomb_mult' => 'Een bom vermenigvuldigt de score met',
            'casino_ddz_rocket_mult' => 'De raket (beide jokers, 王炸) vermenigvuldigt de score met',
            'casino_ddz_max_mult' => 'Hoogste vermenigvuldiging (bod x bommen x raket x 2 voor lente)',
            'casino_ddz_turn_seconds' => 'Bedenktijd per beurt (seconden; daarna speelt de computer)',
            'casino_ddz_result_seconds' => 'Uitslag tonen voor de volgende hand (seconden)',
        ],
        'Schaken' => [
            'casino_board_minutes' => 'Bedenktijd per speler (minuten, schaken en Chinees schaak)',
            'casino_board_increment' => 'Extra seconden per zet',
            'casino_board_away_seconds' => 'Weg tijdens de partij: na zoveel seconden verloren',
            'casino_board_result_seconds' => 'Uitslag tonen voor de volgende partij (seconden)',
            'casino_board_solo_win_easy' => 'Solo, makkelijk: winst bovenop de inzet (procent van de inzet)',
            'casino_board_solo_win_normal' => 'Solo, normaal: winst bovenop de inzet (procent)',
            'casino_board_solo_win_hard' => 'Solo, moeilijk: winst bovenop de inzet (procent)',
        ],
    ];

    public static function get(string $key): string
    {
        $value = trim(c_key($key));
        return $value !== '' ? $value : (self::DEFAULTS[$key] ?? '');
    }

    public static function int(string $key, int $min = 0, int $max = PHP_INT_MAX): int
    {
        return max($min, min($max, (int)self::get($key)));
    }

    public static function float(string $key, float $min = 0, float $max = 1000): float
    {
        return max($min, min($max, (float)str_replace(',', '.', self::get($key))));
    }

    public static function bool(string $key): bool
    {
        return (int)self::get($key) > 0;
    }

    /** "4:60,5:30" => [4 => 60, 5 => 30], only totals 4 t/m 17. */
    public static function diceTotals(): array
    {
        $result = [];
        foreach (preg_split('/[\s,;]+/', self::get('casino_dice_payout_totals'), -1, PREG_SPLIT_NO_EMPTY) as $part) {
            if (preg_match('/^(\d+):(\d+(?:\.\d+)?)$/', $part, $m) && (int)$m[1] >= 4 && (int)$m[1] <= 17) {
                $result[(int)$m[1]] = (float)$m[2];
            }
        }
        ksort($result);
        return $result;
    }

    /** Paytable of the fruit machine: symbol (or cherry2, cherry1) => multiplier of the line bet. */
    public static function slotPaytable(): array
    {
        $result = [];
        foreach ([self::get('casino_slot_paytable'), self::DEFAULTS['casino_slot_paytable']] as $source) {
            foreach (preg_split('/[\s,;]+/', $source, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                if (preg_match('/^([a-z0-9]+):(\d+(?:\.\d+)?)$/', $part, $m) && self::isSlotKey($m[1]) && !isset($result[$m[1]])) {
                    $result[$m[1]] = (float)$m[2];
                }
            }
        }
        return $result;
    }

    private static function isSlotKey(string $key): bool
    {
        return in_array($key, array_merge(CasinoSlotModel::SYMBOLS, ['cherry2', 'cherry1']), true);
    }

    /** Cleans the posted settings form; only known keys are kept. */
    public static function postData(array $post): array
    {
        $data = [];
        foreach (self::DEFAULTS as $key => $default) {
            if (!array_key_exists($key, $post)) {
                continue;
            }
            $value = trim((string)$post[$key]);
            if ($key === 'casino_dice_payout_totals') {
                $pairs = [];
                foreach (preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) as $part) {
                    if (preg_match('/^(\d+):(\d+(?:[.,]\d+)?)$/', $part, $m) && (int)$m[1] >= 4 && (int)$m[1] <= 17) {
                        $pairs[(int)$m[1]] = (int)$m[1] . ':' . str_replace(',', '.', $m[2]);
                    }
                }
                ksort($pairs);
                $value = implode(',', $pairs);
            } elseif ($key === 'casino_slot_paytable') {
                $pairs = [];
                foreach (preg_split('/[\s,;]+/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY) as $part) {
                    if (preg_match('/^([a-z0-9]+):(\d+(?:[.,]\d+)?)$/', $part, $m) && self::isSlotKey($m[1])) {
                        $pairs[$m[1]] = $m[1] . ':' . str_replace(',', '.', $m[2]);
                    }
                }
                $value = implode(',', $pairs);
            } elseif ($key === 'casino_bj_payout') {
                $value = (string)max(0, (float)str_replace(',', '.', $value));
            } elseif ($key !== 'casino_google_client_id') {
                $value = (string)max(0, (int)$value);
            }
            $data[$key] = $value;
        }
        return $data;
    }
}
