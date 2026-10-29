<?php
// Casino texts. Placeholders like {amount} are filled in by CasinoLang::t() / Casino.t().
// Keys with casino_js_ are also sent to the game scripts.
$lang["casino_title"] = "Casino";
$lang["casino_login_title"] = "Casino - anmelden";
$lang["casino_login_intro"] = "Würfel und Blackjack mit anderen Spielern. Du spielst mit virtuellen Chips.";
$lang["casino_login_not_configured"] = "Die Anmeldung ist noch nicht eingerichtet (Verwaltung > Casino > Einstellung > Google Client ID).";
$lang["casino_login_failed"] = "Die Anmeldung mit Google ist fehlgeschlagen.";
$lang["casino_login_blocked"] = "Dein Konto wurde gesperrt.";
$lang["casino_logged_out"] = "Du wurdest abgemeldet.";
$lang["casino_logout"] = "Abmelden";
$lang["casino_chips"] = "Chips";
$lang["casino_language"] = "Sprache";
$lang["casino_choose_table"] = "Wähle einen Tisch";
$lang["casino_no_tables"] = "Es sind noch keine Tische geöffnet.";
$lang["casino_bonus_wait"] = "Bonus in {hours} Std.";
$lang["casino_bonus_claim"] = "{amount} Bonuschips holen";
$lang["casino_bonus_received"] = "{amount} Chips erhalten!";
$lang["casino_bonus_off"] = "Der tägliche Bonus ist ausgeschaltet.";
$lang["casino_bonus_taken"] = "Der Bonus wurde heute schon abgeholt.";
$lang["casino_history"] = "Verlauf";
$lang["casino_my_history"] = "Mein Verlauf";
$lang["casino_online"] = "{count} online";
$lang["casino_lobby"] = "Lobby";
$lang["casino_loading"] = "Laden...";
$lang["casino_col_time"] = "Zeit";
$lang["casino_col_game"] = "Spiel";
$lang["casino_col_action"] = "Aktion";
$lang["casino_col_chips"] = "Chips";
$lang["casino_col_balance"] = "Guthaben";
$lang["casino_col_details"] = "Details";
$lang["casino_player_fallback"] = "Spieler {id}";

$lang["casino_game_dice"] = "Würfel (Sic Bo)";
$lang["casino_game_blackjack"] = "Blackjack (21)";

$lang["casino_action_register"] = "Neuer Spieler";
$lang["casino_action_login"] = "Anmelden";
$lang["casino_action_logout"] = "Abmelden";
$lang["casino_action_bonus"] = "Täglicher Bonus";
$lang["casino_action_admin"] = "Änderung Verwaltung";
$lang["casino_action_join"] = "Am Tisch";
$lang["casino_action_leave"] = "Tisch verlassen";
$lang["casino_action_bet"] = "Einsatz";
$lang["casino_action_refund"] = "Einsatz zurück";
$lang["casino_action_double"] = "Verdoppeln";
$lang["casino_action_hit"] = "Karte";
$lang["casino_action_stand"] = "Stehen";
$lang["casino_action_timeout"] = "Zeit abgelaufen";
$lang["casino_action_roll"] = "Wurf";
$lang["casino_action_deal"] = "Geben";
$lang["casino_action_win"] = "Gewonnen";
$lang["casino_action_lose"] = "Verloren";
$lang["casino_action_push"] = "Unentschieden";
$lang["casino_action_blackjack"] = "Blackjack";

$lang["casino_err_invalid_bet"] = "Ungültiger Einsatz.";
$lang["casino_err_no_chips"] = "Nicht genug Chips.";
$lang["casino_err_busy"] = "Der Tisch ist gerade belegt, bitte versuche es erneut.";
$lang["casino_err_table_not_found"] = "Tisch nicht gefunden.";
$lang["casino_err_table_closed"] = "Dieser Tisch ist geschlossen.";
$lang["casino_err_wait_round"] = "Warte auf die nächste Runde.";
$lang["casino_err_unknown_spot"] = "Unbekanntes Feld.";
$lang["casino_err_min_bet"] = "Mindesteinsatz ist {amount}.";
$lang["casino_err_max_spot"] = "Höchstens {amount} pro Feld.";
$lang["casino_err_max_bet"] = "Höchsteinsatz ist {amount}.";
$lang["casino_err_nothing_to_clear"] = "Kein Einsatz zum Zurücknehmen.";
$lang["casino_err_unknown_action"] = "Unbekannte Aktion.";
$lang["casino_err_table_full"] = "Der Tisch ist voll.";
$lang["casino_err_sit_first"] = "Nimm zuerst am Tisch Platz.";
$lang["casino_err_not_your_turn"] = "Du bist nicht am Zug.";
$lang["casino_err_double_first_two"] = "Verdoppeln geht nur mit den ersten zwei Karten.";

$lang["casino_dice_small"] = "Klein";
$lang["casino_dice_big"] = "Groß";
$lang["casino_dice_odd"] = "Ungerade";
$lang["casino_dice_even"] = "Gerade";
$lang["casino_dice_triple"] = "Jeder Drilling";
$lang["casino_dice_triple_hint"] = "3 gleiche";
$lang["casino_dice_per_die"] = "1:1 pro Würfel";
$lang["casino_dice_triple_rule"] = "Groß, Klein, Gerade und Ungerade verlieren bei einem Drilling.";
$lang["casino_clear_bet"] = "Einsatz zurück";
$lang["casino_players_at_table"] = "Spieler am Tisch";
$lang["casino_last_rolls"] = "Letzte Würfe";

$lang["casino_bj_dealer"] = "Bank";
$lang["casino_bj_bet"] = "Setzen";
$lang["casino_bj_back"] = "Zurück";
$lang["casino_bj_hit"] = "Karte";
$lang["casino_bj_stand"] = "Stehen";
$lang["casino_bj_double"] = "Verdoppeln";
$lang["casino_bj_sit_hint"] = "Klicke auf einen freien Platz, um mitzuspielen.";

$lang["casino_js_error"] = "Etwas ist schiefgelaufen.";
$lang["casino_js_no_connection"] = "Keine Verbindung zum Server.";
$lang["casino_js_you"] = "du";
$lang["casino_js_round_n"] = "Runde {n}";
$lang["casino_js_place_bet_start"] = "Setze, um die Runde zu starten";
$lang["casino_js_place_bet"] = "Bitte setzen...";
$lang["casino_js_rolling"] = "Würfeln...";
$lang["casino_js_total"] = "Summe {total}";
$lang["casino_js_you_win"] = "Du gewinnst {amount}!";
$lang["casino_js_you_lose"] = "Du verlierst {amount}";
$lang["casino_js_push"] = "Unentschieden";
$lang["casino_js_won_toast"] = "Gewonnen: +{amount}";
$lang["casino_js_nobody"] = "Noch niemand";
$lang["casino_js_no_rolls"] = "Noch keine Würfe";
$lang["casino_js_big"] = "groß";
$lang["casino_js_small"] = "klein";
$lang["casino_js_triple"] = "Drilling";
$lang["casino_js_rules"] = "Einsatz {min} - {max} · Blackjack zahlt {payout}:1 · {soft17}";
$lang["casino_js_hits_soft17"] = "Bank zieht bei Soft 17";
$lang["casino_js_stands_soft17"] = "Bank steht bei Soft 17";
$lang["casino_js_take_seat"] = "Platz nehmen";
$lang["casino_js_free"] = "frei";
$lang["casino_js_win"] = "Gewonnen";
$lang["casino_js_lose"] = "Verloren";
$lang["casino_js_blackjack"] = "Blackjack!";
$lang["casino_js_bust"] = "Überkauft";
$lang["casino_js_waiting_bets"] = "Warten auf Einsätze";
$lang["casino_js_betting_soon"] = "Setzen... gleich werden die Karten gegeben";
$lang["casino_js_your_bet_wait"] = "Dein Einsatz: {amount} · warten auf die anderen Spieler";
$lang["casino_js_your_turn"] = "Du bist am Zug!";
$lang["casino_js_turn_of"] = "{name} ist am Zug";
$lang["casino_js_dealer_result"] = "Bank: {value}";
$lang["casino_js_dealer_bust"] = "Bank: {value} (überkauft)";
$lang["casino_js_online"] = "{count} online";
$lang["casino_js_shaker_title"] = "SIC BO";
$lang["casino_js_tap_skip"] = "Tippen zum Überspringen";
$lang["casino_js_tap_continue"] = "Tippen zum Weitermachen";

// roulette
$lang["casino_game_roulette"] = "Roulette";
$lang["casino_action_spin"] = "Drehung";
$lang["casino_rl_low"] = "1 - 18";
$lang["casino_rl_high"] = "19 - 36";
$lang["casino_rl_dozen_1"] = "1. Dutzend";
$lang["casino_rl_dozen_2"] = "2. Dutzend";
$lang["casino_rl_dozen_3"] = "3. Dutzend";
$lang["casino_rl_zero_rule"] = "Bei 0 verlieren alle Außenwetten. Eine Zahl zahlt 35:1, Dutzend und Kolonne 2:1, der Rest 1:1.";
$lang["casino_last_numbers"] = "Letzte Zahlen";
$lang["casino_js_red"] = "Rot";
$lang["casino_js_black"] = "Schwarz";
$lang["casino_js_green"] = "Grün";
$lang["casino_js_odd"] = "Ungerade";
$lang["casino_js_even"] = "Gerade";
$lang["casino_js_spinning"] = "Das Rad dreht sich...";
$lang["casino_js_no_more_bets"] = "Nichts geht mehr";
$lang["casino_js_wheel_title"] = "ROULETTE";

// confirm before leaving
$lang["casino_js_confirm_logout"] = "Möchtest du dich wirklich abmelden?";
$lang["casino_js_confirm_leave"] = "Du hast noch einen Einsatz oder einen Platz an diesem Tisch. Möchtest du den Tisch wirklich verlassen?";
$lang["casino_js_confirm_leave_hint"] = "Offene Einsätze gehen zurück; eine laufende Hand steht automatisch.";
$lang["casino_js_yes"] = "Ja";
$lang["casino_js_cancel"] = "Abbrechen";

// dice board sections
$lang["casino_dice_sec_main"] = "Groß · Klein · Ungerade · Gerade";
$lang["casino_dice_sec_doubles"] = "Bestimmter Pasch";
$lang["casino_dice_sec_triples"] = "Bestimmter Drilling";
$lang["casino_dice_sec_totals"] = "Summe";
$lang["casino_dice_sec_combos"] = "Kombination aus zwei";
$lang["casino_dice_sec_singles"] = "Einzelne Zahl";
$lang["casino_dice_singles_rule"] = "Wähle eine Zahl: zahlt 1:1, {two}:1 oder {three}:1, wenn sie auf einem, zwei oder drei Würfeln liegt.";

// win / loss record at the table
$lang["casino_my_records"] = "Meine Ergebnisse";
$lang["casino_rec_round"] = "Runde";
$lang["casino_rec_result"] = "Ergebnis";
$lang["casino_rec_bet"] = "Einsatz";
$lang["casino_rec_net"] = "Gewinn / Verlust";
$lang["casino_js_rec_total"] = "Letzte {n}:";
$lang["casino_js_rec_none"] = "Du hast an diesem Tisch noch nicht gespielt.";

// animation and sound buttons
$lang["casino_anim_toggle"] = "Animationen ein/aus";
$lang["casino_sound_toggle"] = "Ton ein/aus";

// fruit machine
$lang["casino_game_slot"] = "Früchte-Automat";
$lang["casino_action_jackpot"] = "Jackpot";
$lang["casino_sl_jackpot"] = "JACKPOT";
$lang["casino_sl_line_bet"] = "Einsatz pro Linie";
$lang["casino_sl_total_bet"] = "Gesamteinsatz";
$lang["casino_sl_spin"] = "DREHEN";
$lang["casino_sl_paytable"] = "Gewinntabelle";
$lang["casino_sl_cherry2"] = "2 Kirschen von links";
$lang["casino_sl_cherry1"] = "1 Kirsche links";
$lang["casino_sl_rule"] = "Wähle 1 bis 5 Gewinnlinien (Linie 1 ist die mittlere). Eine Linie zahlt x-mal den Einsatz pro Linie. 777 auf der mittleren Linie gewinnt auch den Jackpot: beim Höchsteinsatz pro Linie den ganzen Topf, sonst einen Anteil im Verhältnis.";
$lang["casino_sl_recent"] = "Letzte Gewinne an diesem Automaten";
$lang["casino_js_sl_spinning"] = "Dreht...";
$lang["casino_js_sl_press_spin"] = "Wähle deinen Einsatz und drücke DREHEN (oder die Leertaste)";
$lang["casino_js_sl_no_win"] = "Kein Gewinn, versuch es noch einmal";
$lang["casino_js_sl_line"] = "Linie {n}";
$lang["casino_js_sl_win"] = "Gewonnen: {amount}";
$lang["casino_js_sl_big_win"] = "GROSSER GEWINN +{amount}";
$lang["casino_js_sl_jackpot_win"] = "JACKPOT! +{amount}";

// Texas Hold'em
$lang["casino_game_holdem"] = 'Texas Hold\'em Poker';
$lang["casino_pk_rules"] = 'No Limit Texas Hold\'em · Blinds {sb}/{bb} · Buy-in {min} - {max}';
$lang["casino_action_buyin"] = 'Buy-in';
$lang["casino_action_cashout"] = 'Auszahlung';
$lang["casino_action_blind"] = 'Blind';
$lang["casino_action_fold"] = 'Fold';
$lang["casino_action_check"] = 'Check';
$lang["casino_action_call"] = 'Call';
$lang["casino_action_raise"] = 'Raise';
$lang["casino_action_allin"] = 'All-in';
$lang["casino_action_rake"] = 'Rake';
$lang["casino_err_pk_buyin"] = 'Das Buy-in muss zwischen {min} und {max} liegen.';
$lang["casino_err_pk_cannot_check"] = 'Checken geht nicht, es steht ein Einsatz.';
$lang["casino_err_pk_min_raise"] = 'Erhöhe mindestens auf {amount}.';
$lang["casino_err_pk_cannot_raise"] = 'Du kannst jetzt nicht mehr erhöhen.';
$lang["casino_js_pk_fold"] = 'Fold';
$lang["casino_js_pk_check"] = 'Check';
$lang["casino_js_pk_call"] = 'Call {amount}';
$lang["casino_js_pk_bet"] = 'Setzen {amount}';
$lang["casino_js_pk_raise"] = 'Erhöhen auf {amount}';
$lang["casino_js_pk_allin"] = 'All-in';
$lang["casino_js_pk_min"] = 'Min';
$lang["casino_js_pk_half_pot"] = '½ Pot';
$lang["casino_js_pk_pot"] = 'Pot';
$lang["casino_js_pk_pot_total"] = 'Pot: {amount}';
$lang["casino_js_pk_last_call"] = 'Call';
$lang["casino_js_pk_last_raise"] = 'Raise';
$lang["casino_js_pk_buyin_title"] = 'Wie viele Chips bringst du an den Tisch?';
$lang["casino_js_pk_buyin_hint"] = 'Zwischen {min} und {max}. Was du beim Aufstehen noch hast, geht zurück auf dein Guthaben.';
$lang["casino_js_pk_sit"] = 'Hinsetzen';
$lang["casino_js_pk_sit_hint"] = 'Klicke auf einen freien Platz, um mitzuspielen';
$lang["casino_js_pk_waiting"] = 'Warten auf Spieler (mindestens 2)';
$lang["casino_js_pk_next_hand"] = 'Die nächste Hand beginnt gleich...';
$lang["casino_js_pk_sitting_out"] = 'Spielt ab der nächsten Hand';
$lang["casino_js_pk_leaving"] = 'Steht nach dieser Hand auf';
$lang["casino_js_pk_wins"] = '{name} gewinnt {amount}';
$lang["casino_js_pk_with"] = 'mit {hand}';
$lang["casino_js_pk_your_hand"] = 'Deine Hand: {hand}';
$lang["casino_js_pk_blinds"] = 'Blinds {sb}/{bb}';
$lang["casino_js_hand_high_card"] = 'Höchste Karte';
$lang["casino_js_hand_pair"] = 'Paar';
$lang["casino_js_hand_two_pair"] = 'Zwei Paare';
$lang["casino_js_hand_trips"] = 'Drilling';
$lang["casino_js_hand_straight"] = 'Straße';
$lang["casino_js_hand_flush"] = 'Flush';
$lang["casino_js_hand_full_house"] = 'Full House';
$lang["casino_js_hand_quads"] = 'Vierling';
$lang["casino_js_hand_straight_flush"] = 'Straight Flush';
$lang["casino_js_hand_royal_flush"] = 'Royal Flush';

// bingo
$lang["casino_game_bingo"] = 'Bingo';
$lang["casino_action_line"] = 'Linie';
$lang["casino_action_bingo"] = 'Bingo';
$lang["casino_err_bg_max_cards"] = 'Höchstens {max} Karten pro Runde.';
$lang["casino_bg_pot"] = 'Gewinntopf';
$lang["casino_bg_line_prize"] = 'Erste Linie';
$lang["casino_bg_full_prize"] = 'Volle Karte (Bingo)';
$lang["casino_bg_my_cards"] = 'Meine Karten';
$lang["casino_bg_board"] = 'Gezogene Zahlen';
$lang["casino_bg_cancel"] = 'Karten zurück';
$lang["casino_bg_history"] = 'Vorherige Runden';
$lang["casino_bg_rules"] = 'Eine Karte kostet {price}. Zahlen werden automatisch markiert. Die erste Linie (Reihe, Spalte oder Diagonale) gewinnt {line}% des Topfs, die erste volle Karte den Rest. Der Topf ist {payout}% der verkauften Karten; Gewinner mit derselben Kugel teilen.';
$lang["casino_js_bg_buy_n"] = '+{n} Karte ({amount})';
$lang["casino_js_bg_buy_start"] = 'Kaufe eine Karte, um die Runde zu starten';
$lang["casino_js_bg_buying"] = 'Karten kaufen... die Ziehung beginnt gleich';
$lang["casino_js_bg_drawing"] = 'Ziehung: Kugel {n} von 75';
$lang["casino_js_bg_line_won"] = 'LINIE! +{amount}';
$lang["casino_js_bg_bingo_won"] = 'BINGO! +{amount}';
$lang["casino_js_bg_line_by"] = 'Linie: {names}';
$lang["casino_js_bg_full_by"] = 'Bingo: {names}';
$lang["casino_js_bg_line_badge"] = 'Linie';
$lang["casino_js_bg_to_go"] = 'noch {n}';
$lang["casino_js_bg_no_cards"] = 'Du hast in dieser Runde keine Karten.';
$lang["casino_js_bg_cards_n"] = '{n} Karten';
$lang["casino_js_bg_round_line"] = 'Runde {round}: {balls} Kugeln';

// what a bet can win
$lang["casino_js_can_win"] = 'gewinnt +{amount}';
$lang["casino_js_can_win_max"] = 'bis +{amount}';
$lang["casino_js_sl_jackpot_short"] = 'Jackpot';

// solo tables
$lang["casino_solo"] = 'Solo';
$lang["casino_err_solo_no_bets"] = 'Setze zuerst einen Einsatz.';
$lang["casino_js_solo_go_dice"] = 'Würfeln';
$lang["casino_js_solo_go_roulette"] = 'Drehen';
$lang["casino_js_solo_hint"] = 'Setze und starte selbst, ohne Wartezeit';
$lang["casino_js_solo_ready"] = 'Bereit? Drücke den Knopf (oder die Leertaste)';

// solo blackjack
$lang["casino_js_solo_go_bj"] = 'Geben';

// fruit machine lines
$lang["casino_sl_lines"] = 'Linien';

// bingo record
$lang["casino_js_bg_balls"] = '{n} Kugeln';

// Sic Bo casino board
$lang["casino_dice_layout_casino"] = 'Casino-Tisch';
$lang["casino_dice_layout_list"] = 'Nach Bereich';
$lang["casino_dice_pays"] = '1 zahlt {n}';
$lang["casino_dice_small_desc"] = '4 bis 10 · verliert bei Drilling';
$lang["casino_dice_big_desc"] = '11 bis 17 · verliert bei Drilling';
$lang["casino_dice_double_n"] = 'Pasch {n}';
$lang["casino_dice_triple_n"] = 'Drilling {n}';
$lang["casino_dice_undo_last"] = 'Letzten Einsatz zurück';
$lang["casino_dice_double_bets"] = 'Verdoppeln';
$lang["casino_dice_rebet"] = 'Wiederholen';

// webmaster login
$lang["casino_login_backoffice"] = 'Weiter als Webmaster ({name})';

// 
$lang["casino_js_win_congrats"] = 'Glückwunsch, du gewinnst {amount}';

// 
$lang["casino_fullscreen"] = 'Vollbild';

// 
$lang["casino_game_doudizhu"] = 'Dou Dizhu (Gutsherr)';
$lang["casino_ddz_base"] = 'Basis';
$lang["casino_ddz_bid"] = 'Gebot';
$lang["casino_ddz_mult"] = 'Faktor';
$lang["casino_ddz_rules"] = 'Drei Spieler: der Gutsherr gegen zwei Bauern. Biete 1 bis 3 Punkte; der Höchstbietende wird Gutsherr und bekommt die 3 Bodenkarten. Wer zuerst alle Karten los ist, gewinnt für seine Seite. Punkte = Basis x Gebot, x{bomb} pro Bombe, x{rocket} für die Rakete (beide Joker), x2 für einen Frühling. Einsatz {min} - {max}.';
$lang["casino_ddz_bot_1"] = 'Computer A';
$lang["casino_ddz_bot_2"] = 'Computer B';
$lang["casino_err_ddz_bid"] = 'Biete mehr als das aktuelle Gebot (1 bis 3) oder passe.';
$lang["casino_err_ddz_must_lead"] = 'Du spielst aus: lege Karten.';
$lang["casino_err_ddz_cards"] = 'Diese Karten hast du nicht.';
$lang["casino_err_ddz_invalid"] = 'Diese Karten sind keine gültige Kombination.';
$lang["casino_err_ddz_lower"] = 'Spiele dieselbe Art Kombination, aber höher (oder eine Bombe).';
$lang["casino_err_ddz_pick"] = 'Wähle zuerst die Karten, die du spielen willst.';
$lang["casino_err_ddz_stack"] = 'Du hast zu wenig Chips am Tisch für den Grundeinsatz.';
$lang["casino_action_bid"] = 'Gebot';
$lang["casino_action_landlord"] = 'Gutsherr';
$lang["casino_action_play"] = 'Karten gespielt';
$lang["casino_action_pass"] = 'Passen';
$lang["casino_js_ddz_pass"] = 'Passen';
$lang["casino_js_ddz_bid_n"] = '{n} Punkt';
$lang["casino_js_ddz_no_bid"] = 'Nicht bieten';
$lang["casino_js_ddz_landlord"] = 'Gutsherr';
$lang["casino_js_ddz_farmer"] = 'Bauer';
$lang["casino_js_ddz_auto_on"] = 'Computer spielt';
$lang["casino_js_ddz_auto"] = 'Computer spielen lassen';
$lang["casino_js_ddz_auto_off"] = 'Selbst spielen';
$lang["casino_js_ddz_solo_start"] = 'Nimm Platz und spiele gegen zwei Computerspieler.';
$lang["casino_js_ddz_solo_ready"] = 'Bereit? Drücke Geben.';
$lang["casino_js_ddz_wait_players"] = 'Warten auf drei Spieler...';
$lang["casino_js_ddz_starting"] = 'Die Karten werden gegeben...';
$lang["casino_js_ddz_your_bid"] = 'Du bist dran: willst du Gutsherr werden?';
$lang["casino_js_ddz_bidding"] = '{name} bietet...';
$lang["casino_js_ddz_landlord_wins"] = 'Der Gutsherr gewinnt';
$lang["casino_js_ddz_farmers_wins"] = 'Die Bauern gewinnen';
$lang["casino_js_ddz_spring"] = 'Frühling!';
$lang["casino_js_ddz_solo_sit"] = 'Platz nehmen';
$lang["casino_js_ddz_next_hand"] = 'Nächste Runde';
$lang["casino_js_ddz_deal"] = 'Geben';
$lang["casino_js_ddz_hint"] = 'Tipp';
$lang["casino_js_ddz_play"] = 'Spielen';
$lang["casino_js_ddz_no_hint"] = 'Nichts ist höher: passe.';

// 
$lang["casino_game_chess"] = 'Schach';
$lang["casino_game_xiangqi"] = 'Chinesisches Schach (Xiangqi)';
$lang["casino_err_board_move"] = 'Dieser Zug ist nicht erlaubt.';
$lang["casino_board_bot_easy"] = 'Computer (leicht)';
$lang["casino_board_bot_normal"] = 'Computer (normal)';
$lang["casino_board_bot_hard"] = 'Computer (schwer)';
$lang["casino_board_rules_chess"] = 'Einsatz {stake} pro Partie; der Sieger bekommt beide Einsätze, Remis gibt den Einsatz zurück. Matt, Aufgeben, Zeitüberschreitung oder Weggehen verliert. Remis: Patt, 50 Züge, dreimal dieselbe Stellung, zu wenig Material oder vereinbart.';
$lang["casino_board_rules_xiangqi"] = 'Einsatz {stake} pro Partie; der Sieger bekommt beide Einsätze, Remis gibt den Einsatz zurück. Wer keinen Zug mehr hat (Matt oder kein Zug), verliert, ebenso Aufgeben, Zeitüberschreitung oder Weggehen. Remis: dreimal dieselbe Stellung, 60 Züge ohne Schlagen oder keine Angriffsfiguren mehr.';
$lang["casino_js_board_moves"] = 'Züge';
$lang["casino_js_board_moves_short"] = 'Züge';
$lang["casino_js_board_white"] = 'Weiß';
$lang["casino_js_board_black"] = 'Schwarz';
$lang["casino_js_board_red"] = 'Rot';
$lang["casino_js_board_away"] = 'weg';
$lang["casino_js_board_waiting"] = 'Warten auf einen Gegner...';
$lang["casino_js_board_starting"] = 'Die Partie beginnt...';
$lang["casino_js_board_your_move"] = 'Du bist am Zug';
$lang["casino_js_board_check"] = 'Schach!';
$lang["casino_js_board_draw"] = 'Remis';
$lang["casino_js_board_you_won"] = 'Du hast gewonnen!';
$lang["casino_js_board_you_lost"] = 'Du hast verloren';
$lang["casino_js_board_wins"] = '{name} gewinnt';
$lang["casino_js_board_reason_checkmate"] = 'Matt';
$lang["casino_js_board_reason_stalemate"] = 'Patt';
$lang["casino_js_board_reason_stalemate_loss"] = 'kein Zug mehr';
$lang["casino_js_board_reason_fifty"] = '50 Züge';
$lang["casino_js_board_reason_sixty"] = '60 Züge ohne Schlagen';
$lang["casino_js_board_reason_repetition"] = 'dreimal dieselbe Stellung';
$lang["casino_js_board_reason_material"] = 'zu wenig Material';
$lang["casino_js_board_reason_resign"] = 'aufgegeben';
$lang["casino_js_board_reason_time"] = 'Zeit abgelaufen';
$lang["casino_js_board_reason_abandon"] = 'Partie verlassen';
$lang["casino_js_board_reason_agreed"] = 'vereinbart';
$lang["casino_js_board_resign"] = 'Aufgeben';
$lang["casino_js_board_resign_confirm"] = 'Wirklich aufgeben? Du verlierst deinen Einsatz.';
$lang["casino_js_board_offer_draw"] = 'Remis anbieten';
$lang["casino_js_board_accept_draw"] = 'Remis annehmen';
$lang["casino_js_board_draw_offered"] = 'du hast Remis angeboten';
$lang["casino_js_board_draw_wanted"] = 'dein Gegner bietet Remis an';
$lang["casino_js_board_declined"] = 'der Computer spielt weiter';
$lang["casino_js_board_solo_hint"] = 'Wähle deine Farbe und die Stufe und spiele gegen den Computer.';
$lang["casino_js_board_play_as"] = 'Ich spiele';
$lang["casino_js_board_level"] = 'Stufe (Gewinn)';
$lang["casino_js_board_level_1"] = 'Leicht';
$lang["casino_js_board_level_2"] = 'Normal';
$lang["casino_js_board_level_3"] = 'Schwer';
$lang["casino_js_board_start"] = 'Partie beginnen (Einsatz {stake})';
$lang["casino_js_board_promote"] = 'Umwandeln in';
$lang["casino_js_ddz_bomb_fx"] = 'BOMBE! x{n}';
$lang["casino_js_ddz_rocket_fx"] = 'RAKETE! x{n}';
